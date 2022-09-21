<?php
/**
 * Spoon plugin for Craft CMS
 *
 * Bend your Matrix fields with block groups & tabs.
 *
 * @author    Double Secret Agency
 * @link      https://plugins.doublesecretagency.com/
 * @copyright Copyright (c) 2018, 2022 Double Secret Agency
 */

namespace doublesecretagency\spoon\services;

use Craft;
use craft\base\Component;
use craft\base\Field;
use craft\base\PluginInterface;
use craft\db\Table;
use craft\events\ConfigEvent;
use craft\helpers\Db;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use craft\helpers\StringHelper;
use craft\models\FieldLayout;
use doublesecretagency\spoon\errors\BlockTypeNotFoundException;
use doublesecretagency\spoon\models\BlockType;
use doublesecretagency\spoon\records\BlockType as BlockTypeRecord;
use Exception;
use Throwable;
use yii\db\Transaction;

/**
 * BlockTypes Service
 * @since 3.0.0
 */
class BlockTypes extends Component
{

    /**
     * @var array
     */
    private array $_blockTypesByContext = [];

    /**
     * @var PluginInterface|null
     */
    private ?PluginInterface $_superTablePlugin = null;

    /**
     * @var \verbb\supertable\services\Service|null
     */
    private mixed $_superTableService = null;

    /**
     * @const CONFIG_BLOCKTYPE_KEY
     */
    public const CONFIG_BLOCKTYPE_KEY = 'spoonBlockTypes';

    // ========================================================================= //

    /**
     * Returns a Spoon block type model by its ID.
     *
     * @param int $id
     * @return BlockType|null
     * @throws BlockTypeNotFoundException
     */
    public function getById(int $id): ?BlockType
    {
        $blockTypeRecord = BlockTypeRecord::findOne($id);

        if (!$blockTypeRecord) {
            throw new BlockTypeNotFoundException(Craft::t('spoon', 'No Spoon block type exists with the ID “{id}”', ['id' => $id]));
        }

        return $this->_populateBlockTypeFromRecord($blockTypeRecord);
    }

    /**
     * Returns a single BlockType Model by its context and blockTypeId.
     *
     * @param string|null $context
     * @param int|null $matrixBlockTypeId
     * @return BlockType|null
     */
    public function getBlockType(?string $context = null, ?int $matrixBlockTypeId = null): ?BlockType
    {
        // If no context or ID, bail
        if (!$context || !$matrixBlockTypeId) {
            return null;
        }

        // Get the existing block type record
        $blockTypeRecord = BlockTypeRecord::findOne([
            'context'           => $context,
            'matrixBlockTypeId' => $matrixBlockTypeId
        ]);

        // If no block type record exists, bail
        if (!$blockTypeRecord) {
            return null;
        }

        // Return the block type
        return $this->_populateBlockTypeFromRecord($blockTypeRecord);
    }

    /**
     * Returns a block type by its context.
     *
     * @param string $context
     * @param string|null $groupBy Group by an optional model attribute.
     * @param bool $ignoreSubContext Optionally ignore the sub context (id).
     * @param int|null $fieldId Optionally filter by fieldId.
     * @return array
     */
    public function getByContext(string $context, ?string $groupBy = null, bool $ignoreSubContext = false, ?int $fieldId = null): array
    {

        if ($ignoreSubContext) {

            if ($fieldId !== null) {
                if ($context === 'global') {
                    $condition = [
                        'fieldId' => $fieldId,
                        'context' => 'global'
                    ];
                } else {
                    $condition = [
                        'fieldId' => $fieldId,
                        ['like', 'context', $context.'%', false]
                    ];
                }
            } else {
                if ($context === 'global') {
                    $condition = [
                        'context' => 'global'
                    ];
                } else {
                    $condition = ['like', 'context', $context.'%', false];
                }
            }

        } else {
            $condition = [
                'context' => $context
            ];

            if ($fieldId !== null)
            {
                $condition['fieldId'] = $fieldId;
            }
        }

        $blockTypeRecords = BlockTypeRecord::find()
            ->where($condition)
            ->orderBy(['groupSortOrder' => SORT_ASC, 'sortOrder' => SORT_ASC])
            ->all();

        if ($blockTypeRecords) {

            foreach ($blockTypeRecords as $blockTypeRecord) {
                $blockType = $this->_populateBlockTypeFromRecord($blockTypeRecord);
                if ($blockType) {
                    $this->_blockTypesByContext[$context][$blockType->id] = $blockType;
                }
            }

        } else {
            return [];
        }

        if ($groupBy !== null) {
            $return = [];

            foreach ($this->_blockTypesByContext[$context] as $blockType)
            {
                $return[$blockType->$groupBy][] = $blockType;
            }

            return $return;
        }

        return $this->_blockTypesByContext[$context];

    }

    /**
     * Saves our version of a block type into the project config.
     *
     * @param BlockType $blockType
     * @return bool
     * @throws Exception
     */
    public function save(BlockType $blockType): bool
    {
        $isNew = !$blockType->id;

        // Ensure the block type has a UID
        if ($isNew) {
            $blockType->uid = StringHelper::UUID();
        } else if (!$blockType->uid) {
            $existingBlockTypeRecord = BlockTypeRecord::findOne($blockType->id);

            if (!$existingBlockTypeRecord) {
                throw new BlockTypeNotFoundException("No Spoon Block Type exists with the ID “{$blockType->id}”");
            }

            $blockType->uid = $existingBlockTypeRecord->uid;
        }

        // Make sure it validates
        if (!$blockType->validate()) {
            return false;
        }

        // Save it to the project config
        $configData = [
            'groupName' => $blockType->groupName,
            'groupSortOrder' => $blockType->groupSortOrder,
            'sortOrder' => $blockType->sortOrder,
            'context' => $blockType->context,
            'field' => $blockType->getField()->uid,
            'matrixBlockType' => $blockType->getBlockType()->uid,
        ];

        // Handle any currently attached field layouts
        /** @var FieldLayout $fieldLayout */
        $fieldLayout = $blockType->getFieldLayout();

        if ($blockType->fieldLayoutId && $fieldLayout && $fieldLayout->uid) {
            $fieldLayoutConfig = $fieldLayout->getConfig();

            $layoutUid = $fieldLayout->uid;

            $configData['fieldLayout'] = [
                $layoutUid => $fieldLayoutConfig
            ];
        } else {
            $configData['fieldLayout'] = null;
        }

        $configPath = self::CONFIG_BLOCKTYPE_KEY . '.' . $blockType->uid;

        Craft::$app->projectConfig->set($configPath, $configData);

        if ($isNew) {
            $blockType->id = Db::idByUid('{{%spoon_blocktypes}}', $blockType->uid);
        }

        return true;
    }

    /**
     * Deletes all the block types for a given context from the project config.
     *
     * @param string|null $context
     * @param int|null $fieldId
     * @return bool|null
     * @throws Exception
     */
    public function deleteByContext(?string $context = null, ?int $fieldId = null): ?bool
    {
        // If no context was provided, bail
        if (!$context) {
            return false;
        }

        // If no field ID was provided, bail
        if (!$fieldId) {
            return false;
        }

        // Get project config services
        $pc = Craft::$app->getProjectConfig();

        // Match only specified field in specified context
        $conditions = [
            'fieldId' => $fieldId,
            'context' => $context
        ];

        // Get UIDs of all existing block type records
        $uids = BlockTypeRecord::find()
            ->select('uid')
            ->where($conditions)
            ->column();

        // Delete existing block type records from database
        BlockTypeRecord::deleteAll($conditions);

        // Loop through UIDs
        foreach ($uids as $uid) {
            // Get path for project config file
            $path = self::CONFIG_BLOCKTYPE_KEY.'.'.$uid;
            // Remove project config file
            $pc->remove($path, "Remove block type from Spoon data");
        }

        // Return success
        return true;
    }

    // Project config methods
    // ========================================================================= //

    /**
     * Handles a changed block type and saves it to the database.
     *
     * @param ConfigEvent $event
     * @throws Throwable
     */
    public function handleChangedBlockType(ConfigEvent $event): void
    {
        $fieldsService = Craft::$app->getFields();

        $uid = $event->tokenMatches[0];
        $data = $event->newValue;

        // Make sure fields and sites are processed
        ProjectConfigHelper::ensureAllSitesProcessed();
        ProjectConfigHelper::ensureAllFieldsProcessed();

        // Make sure the field has been synced
        $fieldId = Db::idByUid(Table::FIELDS, $data['field']);
        if ($fieldId === null) {
            Craft::$app->getProjectConfig()->defer($event, [$this, __FUNCTION__]);
            return;
        }

        // Make sure the matrix block type has been synced
        $matrixBlockTypeId = Db::idByUid(Table::MATRIXBLOCKTYPES, $data['matrixBlockType']);
        if ($matrixBlockTypeId === null) {
            Craft::$app->getProjectConfig()->defer($event, [$this, __FUNCTION__]);
            return;
        }

        $db = Craft::$app->getDb();

        /** @var Transaction $transaction */
        $transaction = $db->beginTransaction();

        try {
            // Get the record
            $blockTypeRecord = $this->_getBlockTypeRecord($uid);

            // Prep the record with the new data
            $blockTypeRecord->fieldId = $fieldId;
            $blockTypeRecord->matrixBlockTypeId = $matrixBlockTypeId;
            $blockTypeRecord->groupName = $data['groupName'];
            $blockTypeRecord->context = $data['context'];
            $blockTypeRecord->groupSortOrder = $data['groupSortOrder'];
            $blockTypeRecord->sortOrder = $data['sortOrder'];
            $blockTypeRecord->uid = $uid;

            // Handle the field layout
            if (isset($data['fieldLayout']) && $data['fieldLayout'] !== null) {
                $flData = reset($data['fieldLayout']);
                if ($flData) {
                    // Save the field layout
                    $layout = FieldLayout::createFromConfig($flData);
                    $layout->id = $blockTypeRecord->fieldLayoutId;
                    $layout->type = BlockType::class;
                    $layout->uid = key($data['fieldLayout']);
                    $fieldsService->saveLayout($layout);
                    $blockTypeRecord->fieldLayoutId = $layout->id;
                }
            } else if ($blockTypeRecord->fieldLayoutId) {
                // Delete the field layout
                $fieldsService->deleteLayoutById($blockTypeRecord->fieldLayoutId);
                $blockTypeRecord->fieldLayoutId = null;
            }

            // Save the record
            $blockTypeRecord->save(false);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Handles a deleted block type and removes it from the database.
     *
     * @param ConfigEvent $event
     * @throws Throwable
     */
    public function handleDeletedBlockType(ConfigEvent $event): void
    {
        $uid = $event->tokenMatches[0];
        $blockTypeRecord = $this->_getBlockTypeRecord($uid);

        if (!$blockTypeRecord->id) {
            return;
        }

        $db = Craft::$app->getDb();

        /** @var Transaction $transaction */
        $transaction = $db->beginTransaction();
        try {
            // Delete the block type record
            $db->createCommand()
                ->delete('{{%spoon_blocktypes}}', ['id' => $blockTypeRecord->id])
                ->execute();

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    // Public Methods for FLDs on our Block Types
    // ========================================================================= //

    /**
     * Saves a field layout into the project config nested under the block type config.
     *
     * @param BlockType $blockType
     * @return bool
     * @throws Exception
     */
    public function saveFieldLayout(BlockType $blockType): bool
    {
        /** @var FieldLayout $fieldLayout */
        $fieldLayout = $blockType->getFieldLayout();
//        $oldFieldLayoutId = $blockType->fieldLayoutId;

        if ($fieldLayout->uid) {
            $layoutUid = $fieldLayout->uid;
        } else {
            $layoutUid = StringHelper::UUID();
            $fieldLayout->uid = $layoutUid;
        }

        $fieldLayoutConfig = $fieldLayout->getConfig();

        $configPath = self::CONFIG_BLOCKTYPE_KEY . '.' . $blockType->uid . '.fieldLayout';

        Craft::$app->projectConfig->set($configPath, [
            $layoutUid => $fieldLayoutConfig
        ]);

        return true;
    }

    /**
     * Returns an array of fieldLayoutIds indexed by matrixBlockTypeIds
     * for the given context and fieldId combination.
     *
     * @param  string $context required
     * @param  int|bool $fieldId required
     * @return array|false
     */
    public function getFieldLayoutIds(string $context, int|bool $fieldId = false): array|false
    {
        if (!$fieldId)
        {
            return false;
        }

        $blockTypeRecords = BlockTypeRecord::findAll([
            'context' => $context,
            'fieldId' => $fieldId
        ]);

        $return = [];
        foreach ($blockTypeRecords as $blockTypeRecord)
        {
            $return[$blockTypeRecord->matrixBlockTypeId] = $blockTypeRecord->fieldLayoutId;
        }
        return $return;
    }

    // Private Methods
    // ========================================================================= //

    /**
     * Gets a block type's record by uid.
     *
     * @param string $uid
     * @return BlockTypeRecord
     */
    private function _getBlockTypeRecord(string $uid): BlockTypeRecord
    {
        $record = BlockTypeRecord::findOne(['uid' => $uid]);
        return $record ?? new BlockTypeRecord();
    }

    /**
     * Populates a BlockTypeModel with attributes from a BlockTypeRecord.
     *
     * @param BlockTypeRecord $blockTypeRecord
     * @return BlockType|null
     */
    private function _populateBlockTypeFromRecord(BlockTypeRecord $blockTypeRecord): ?BlockType
    {
        $blockType = new BlockType($blockTypeRecord->toArray([
            'id',
            'uid',
            'fieldId',
            'matrixBlockTypeId',
            'fieldLayoutId',
            'groupName',
            'context',
            'groupSortOrder',
            'sortOrder'
        ]));

        // Use the fieldId to get the field and save the handle on to the model
        /** @var Field $matrixField */
        $matrixField = Craft::$app->fields->getFieldById($blockType->fieldId);
        if (!$matrixField) {
            return null;
        }
        $blockType->fieldHandle = $matrixField->handle;


        // Super Table support
        if (!$this->_superTablePlugin) {
            $this->_superTablePlugin = Craft::$app->plugins->getPluginByPackageName('verbb/super-table');
        }
        if ($this->_superTablePlugin) {

            if (!$this->_superTableService) {
                $this->_superTableService = new \verbb\supertable\services\Service();
            }

            // If the field is actually inside a SuperTable
            if (strpos($matrixField->context, 'superTableBlockType') === 0) {
                $parts = explode(':', $matrixField->context);
                if (isset($parts[1])) {

                    $superTableBlockTypeId = Db::idByUid('{{%supertableblocktypes}}', $parts[1]);

                    /** @var \verbb\supertable\models\SuperTableBlockTypeModel $superTableBlockType */
                    $superTableBlockType = $this->_superTableService->getBlockTypeById($superTableBlockTypeId);

                    /** @var \verbb\supertable\fields\SuperTableField $superTableField */
                    $superTableField = \Craft::$app->fields->getFieldById($superTableBlockType->fieldId);

                    $blockType->fieldHandle = $superTableField->handle."-".$matrixField->handle;

                    // If the context of _this_ field is inside a Matrix block ... then we need to do more inception
                    if (strpos($superTableField->context, 'matrixBlockType') === 0) {
                        $nestedParts = explode(':', $superTableField->context);
                        if (isset($nestedParts[1])) {

                            $matrixBlockTypeId = Db::idByUid('{{%matrixblocktypes}}', $nestedParts[1]);

                            /** @var craft\models\MatrixBlockType $matrixBlockType */
                            $matrixBlockType = \Craft::$app->matrix->getBlockTypeById($matrixBlockTypeId);

                            /** @var craft\fields\Matrix $globalField */
                            $globalField = \Craft::$app->fields->getFieldById($matrixBlockType->fieldId);

                            $blockType->fieldHandle = $globalField->handle."-".$superTableField->handle."-".$matrixField->handle;
                        }
                    }
                }
            }
        }


        // Save the MatrixBlockTypeModel on to our model
        $blockType->matrixBlockType = $blockType->getBlockType();

        // Save the field layout content on to our model
        if ($blockType->fieldLayoutId) {
            $layout = $blockType->getFieldLayout();

            // Get the tabs
            $tabs = $layout->getTabs();

            // Initialize the fields
            $fields = [];

            // Loop through all tabs
            foreach ($tabs as $tab) {

                // Get the fields for each tab
                foreach ($tab->getElements() as $element) {
                    // Get element field
                    $f = $element->field;
                    // Ensure tab ID is set
                    $f['tabId'] = $tab->id;
                    // Add field
                    $fields[] = $f;
                }

            }

            // Set the field layout model
            $blockType->fieldLayoutModel = [
                'tabs' => $tabs,
                'fields' => $fields
            ];
        }

        return $blockType;
    }

}

<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2018 Angell & Co
 */

namespace angellco\spoon\services;

use angellco\spoon\Spoon;
use angellco\spoon\models\BlockType;
use angellco\spoon\records\BlockType as BlockTypeRecord;
use angellco\spoon\errors\BlockTypeNotFoundException;

use Craft;
use craft\base\Component;
use craft\helpers\Db;
use craft\records\FieldLayout as FieldLayoutRecord;
use craft\records\FieldLayoutField as FieldLayoutFieldRecord;
use craft\records\FieldLayoutTab as FieldLayoutTabRecord;

/**
 * BlockTypes Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Angell & Co
 * @package   Spoon
 * @since     3.0.0
 */
class BlockTypes extends Component
{
    // Private Properties
    // =========================================================================

    private $_blockTypesByContext;

    private $_superTablePlugin;

    private $_superTableService;


    // Public Methods
    // =========================================================================

    /**
     * Returns a Spoon block type model by its ID
     *
     * @param $id
     *
     * @return null
     */
    public function getById($id)
    {
        $blockTypeRecord = BlockTypeRecord::findOne($id);

        if (!$blockTypeRecord) {
            throw new BlockTypeNotFoundException(Craft::t('No Spoon block type exists with the ID “{id}”', ['id' => $id]));
        }

        return $this->_populateBlockTypeFromRecord($blockTypeRecord);
    }

    /**
     * Returns a single BlockType Model by its context and blockTypeId
     *
     * @param bool $context
     * @param bool $matrixBlockTypeId
     *
     * @return bool|null
     */
    public function getBlockType($context = false, $matrixBlockTypeId = false)
    {

        if (!$context || !$matrixBlockTypeId)
        {
            return false;
        }

        $blockTypeRecord = BlockTypeRecord::findOne([
            'context'           => $context,
            'matrixBlockTypeId' => $matrixBlockTypeId
        ]);

        return $this->_populateBlockTypeFromRecord($blockTypeRecord);

    }

    /**
     * Returns a block type by its context.
     *
     * @param string       $context
     * @param null|string  $groupBy Group by an optional model attribute to group by
     * @param bool         $ignoreSubContext Optionally ignore the sub context (id)
     * @param null|integer $fieldId Optinally filter by fieldId
     *
     * @return array
     */
    public function getByContext($context, $groupBy = null, $ignoreSubContext = false, $fieldId = null)
    {

        if ($ignoreSubContext)
        {

            if ($fieldId !== null) {
                $condition = [
                    'fieldId' => $fieldId,
                    ['like', 'context', $context.'%', false]
                ];
            } else {
                $condition = [
                    'like', 'context', $context.'%', false
                ];
            }

            $blockTypeRecords = BlockTypeRecord::find()->where($condition)->all();

        } else
        {
            $condition = [
                'context' => $context
            ];

            if ($fieldId !== null)
            {
                $condition['fieldId'] = $fieldId;
            }

            $blockTypeRecords = BlockTypeRecord::findAll($condition);

        }

        if ($blockTypeRecords)
        {

            foreach ($blockTypeRecords as $blockTypeRecord)
            {
                $blockType = $this->_populateBlockTypeFromRecord($blockTypeRecord);
                $this->_blockTypesByContext[$context][$blockType->id] = $blockType;
            }

        } else
        {
            return [];
        }

        if ($groupBy !== null)
        {
            $return = [];

            foreach ($this->_blockTypesByContext[$context] as $blockType)
            {
                $return[$blockType->$groupBy][] = $blockType;
            }
            return $return;
        } else
        {
            return $this->_blockTypesByContext[$context];
        }

    }

    /**
     * Saves our version of a block type
     *
     * @param BlockType $blockType
     *
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function save(BlockType $blockType)
    {

        if ($blockType->id)
        {
            $blockTypeRecord = BlockTypeRecord::findOne($blockType->id);

            if (!$blockTypeRecord)
            {
                throw new Exception(Craft::t('No Spoon Block Type exists with the ID “{id}”', ['id' => $blockType->id]));
            }
        } else
        {
            $blockTypeRecord = new BlockTypeRecord();
        }

        $blockTypeRecord->fieldId           = $blockType->fieldId;
        $blockTypeRecord->matrixBlockTypeId = $blockType->matrixBlockTypeId;
        $blockTypeRecord->fieldLayoutId     = $blockType->fieldLayoutId;
        $blockTypeRecord->groupName         = $blockType->groupName;
        $blockTypeRecord->context           = $blockType->context;

        $blockTypeRecord->validate();
        $blockType->addErrors($blockTypeRecord->getErrors());

        if (!$blockType->hasErrors())
        {

            $transaction = Craft::$app->getDb()->beginTransaction();
            try {

                // Save it!
                $isNew = $blockTypeRecord->getIsNewRecord();
                $blockTypeRecord->save(false);
                if ($isNew) {
                    $blockType->id = $blockTypeRecord->id;
                }

                // Might as well update our cache of the block type group while we have it.
                $this->_blockTypesByContext[$blockType->context][$blockType->id] = $blockType;

                $transaction->commit();

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }

            return true;
        }

        return false;

    }

    /**
     * Deletes all the block types for a given context
     *
     * @param null|string  $context
     * @param null|integer $fieldId
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function deleteByContext($context = null, $fieldId = null)
    {

        if (!$context)
        {
            return false;
        }

        $transaction = \Craft::$app->getDb()->beginTransaction();
        try {
            $condition = ['context' => $context];

            if ($fieldId)
            {
                $condition['fieldId'] = $fieldId;
            }

            $affectedRows = Craft::$app->getDb()->createCommand()
                ->delete('{{%spoon_blocktypes}}', $condition)
                ->execute();

            $transaction->commit();

            return (bool)$affectedRows;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

    }


    // Public Methods for FLDs on our Block Types
    // =========================================================================

    /**
     * Saves a field layout and attaches it to the given spooned block type
     *
     * @param BlockType $spoonedBlockType
     *
     * @return bool
     */
    public function saveFieldLayout(BlockType $spoonedBlockType)
    {

        // First, get the layout and save the old field layout id for later
        $layout = $spoonedBlockType->getFieldLayout();
        $oldFieldLayoutId = $spoonedBlockType->fieldLayoutId;

        // Second save the layout - replicated from FieldsService::saveLayout()
        // to allow us to retain the $layout->id for our own use
        if ($layout->getTabs())
        {
            $layoutRecord = new FieldLayoutRecord();
            $layoutRecord->type = 'Spoon_BlockType';
            $layoutRecord->save(false);
            $layout->id = $layoutRecord->id;

            foreach ($layout->getTabs() as $tab)
            {
                $tabRecord = new FieldLayoutTabRecord();
                $tabRecord->layoutId  = $layout->id;
                $tabRecord->name      = $tab->name;
                $tabRecord->sortOrder = $tab->sortOrder;
                $tabRecord->save(false);
                $tab->id = $tabRecord->id;

                foreach ($tab->getFields() as $field)
                {
                    /** @var Field $field */
                    $fieldRecord = new FieldLayoutFieldRecord();
                    $fieldRecord->layoutId  = $layout->id;
                    $fieldRecord->tabId     = $tab->id;
                    $fieldRecord->fieldId   = $field->id;
                    $fieldRecord->required  = (bool)$field->required;
                    $fieldRecord->sortOrder = $field->sortOrder;
                    $fieldRecord->save(false);
                }
            }

            // Now we have saved the layout, update the id on the given
            // spooned blocktype model
            $spoonedBlockType->fieldLayoutId = $layout->id;

        } else
        {
            $spoonedBlockType->fieldLayoutId = null;
        }

        // Save our spooned block type again
        if ($this->save($spoonedBlockType))
        {
            // Delete the old field layout
            Craft::$app->fields->deleteLayoutById($oldFieldLayoutId);
            return true;
        }

        return false;
    }


    /**
     * Returns an array of fieldLayoutIds indexed by matrixBlockTypeIds
     * for the given context and fieldId combination
     *
     * @param  string            $context required
     * @param  integer           $fieldId required
     * @return false|array
     */
    public function getFieldLayoutIds($context, $fieldId = false)
    {

        if (!$fieldId)
        {
            return false;
        }

        $blockTypeRecords = BlockTypeRecord::findAll([
            'context' => $context,
            'fieldId' => $fieldId
        ]);

        $return = array();
        foreach ($blockTypeRecords as $blockTypeRecord)
        {
            $return[$blockTypeRecord->matrixBlockTypeId] = $blockTypeRecord->fieldLayoutId;
        }
        return $return;

    }


    // Private Methods
    // =========================================================================

    /**
     * Populates a BlockTypeModel with attributes from a BlockTypeRecord.
     *
     * @param BlockTypeRecord $blockTypeRecord
     *
     * @return null
     */
    private function _populateBlockTypeFromRecord(BlockTypeRecord $blockTypeRecord)
    {

        $blockType = new BlockType($blockTypeRecord->toArray([
            'id',
            'fieldId',
            'matrixBlockTypeId',
            'fieldLayoutId',
            'groupName',
            'context'
        ]));

        if (!$blockTypeRecord) {
            return null;
        }

        // Use the fieldId to get the field and save the handle on to the model
        $matrixField = Craft::$app->fields->getFieldById($blockType->fieldId);
        if (!$matrixField) {
            return null;
        }
        $blockType->fieldHandle = $matrixField->handle;


        // Super Table support
        if (!$this->_superTablePlugin) {
            $this->_superTablePlugin = \Craft::$app->plugins->getPluginByPackageName('verbb/super-table');
        }
        if ($this->_superTablePlugin) {

            if (!$this->_superTableService) {
                $this->_superTableService = new \verbb\supertable\services\SuperTableService();
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
        $layout = $blockType->getFieldLayout();
        $blockType->fieldLayoutModel = [
            'tabs'   => $layout->getTabs(),
            'fields' => $layout->getFields()
        ];

        return $blockType;
    }

}

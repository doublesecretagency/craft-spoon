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

use Craft;
use craft\base\Component;

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


    // Public Methods
    // =========================================================================

//    /**
//     * Returns a single PimpMyMatrix_BlockTypeModel
//     *
//     * @method getBlockType
//     * @param  string       $context           required
//     * @param  int          $matrixBlockTypeId required
//     * @return bool|PimpMyMatrix_BlockTypeModel
//     */
//    public function getBlockType($context = false, $matrixBlockTypeId = false)
//    {
//
//        if (!$context || !$matrixBlockTypeId)
//        {
//            return false;
//        }
//
//        $blockTypeRecord = BlockTypeRecord::model()->findByAttributes(array(
//            'context'           => $context,
//            'matrixBlockTypeId' => $matrixBlockTypeId
//        ));
//
//        return $this->_populateBlockTypeFromRecord($blockTypeRecord);
//
//    }

    /**
     * Returns a block type by its context.
     *
     * @param $context
     * @param $groupBy          Group by an optional model attribute to group by
     * @param $ignoreSubContext Optionally ignore the sub context (id)
     * @return array
     */
    public function getByContext($context, $groupBy = false, $ignoreSubContext = false, $fieldId = false)
    {

        if ($ignoreSubContext)
        {

            if ($fieldId) {
                $condition = [
                    'fieldId' => $fieldId,
                    ['like', 'context', $context.'%', false]
                ];
            } else {
                $condition = [
                    'like', 'context', $context.'%', false
                ];
            }

            $blockTypeRecords = BlockTypeRecord::findAll($condition);

        }
        else
        {
            $condition = [
                'context' => $context
            ];

            if ($fieldId)
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

        }
        else
        {
            return [];
        }

        if ($groupBy)
        {
            $return = [];

            foreach ($this->_blockTypesByContext[$context] as $blockType)
            {
                $return[$blockType->$groupBy][] = $blockType;
            }
            return $return;
        }
        else
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
        }
        else
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
                $blockTypeRecord->save(false);

                // Might as well update our cache of the block type group while we have it.
                $this->_blockTypesByContext[$blockType->context] = $blockType;

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
     * @param string $context
     * @throws \Exception
     * @return bool
     */
    public function deleteByContext($context = false, $fieldId = false)
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
                ->delete('{{%spoon_blocktypes}}',$condition)
                ->execute();

            $transaction->commit();

            return (bool) $affectedRows;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

    }


    // TODO MOVE FLD STUFF OUT

//    /**
//     * Saves a field layout and attaches it to the given pimped block type
//     *
//     * @param  PimpMyMatrix_BlockTypeModel $pimpedBlockType [description]
//     * @return bool
//     */
//    public function saveFieldLayout(PimpMyMatrix_BlockTypeModel $pimpedBlockType)
//    {
//
//        // First, get the layout and save the old field layout id for later
//        $layout = $pimpedBlockType->getFieldLayout();
//        $oldFieldLayoutId = $pimpedBlockType->fieldLayoutId;
//
//        // Second save the layout - replicated from FieldsService::saveLayout()
//        // to allow us to retain the $layout->id for our own use
//        if ($layout->getTabs())
//        {
//            $layoutRecord = new FieldLayoutRecord();
//            $layoutRecord->type = 'PimpMyMatrix_BlockType';
//            $layoutRecord->save(false);
//            $layout->id = $layoutRecord->id;
//
//            foreach ($layout->getTabs() as $tab)
//            {
//                $tabRecord = new FieldLayoutTabRecord();
//                $tabRecord->layoutId  = $layout->id;
//                $tabRecord->name      = $tab->name;
//                $tabRecord->sortOrder = $tab->sortOrder;
//                $tabRecord->save(false);
//                $tab->id = $tabRecord->id;
//
//                foreach ($tab->getFields() as $field)
//                {
//                    $fieldRecord = new FieldLayoutFieldRecord();
//                    $fieldRecord->layoutId  = $layout->id;
//                    $fieldRecord->tabId     = $tab->id;
//                    $fieldRecord->fieldId   = $field->fieldId;
//                    $fieldRecord->required  = $field->required;
//                    $fieldRecord->sortOrder = $field->sortOrder;
//                    $fieldRecord->save(false);
//                    $field->id = $fieldRecord->id;
//                }
//            }
//
//            // Now we have saved the layout, update the id on the given
//            // pimped blocktype model
//            $pimpedBlockType->fieldLayoutId = $layout->id;
//
//        }
//        else
//        {
//            $pimpedBlockType->fieldLayoutId = null;
//        }
//
//        // Save our pimped block type again
//        if ($this->saveBlockType($pimpedBlockType))
//        {
//            // Delete the old field layout
//            craft()->fields->deleteLayoutById($oldFieldLayoutId);
//            return true;
//        }
//        else
//        {
//            return false;
//        }
//    }
//
//
    /**
     * Returns an array of fieldLayoutIds indexed by matrixBlockTypeIds
     * for the given context and fieldId combination
     *
     * @param  string            $context required
     * @param  int               $fieldId required
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
        if (!$blockTypeRecord)
        {
            return null;
        }

        $blockType = BlockType::populateModel($blockTypeRecord);

        // Use the fieldId to get the field and save the handle on to the model
        $matrixField = craft()->fields->getFieldById($blockType->fieldId);
        $blockType->fieldHandle = $matrixField->handle;

        // Save the MatrixBlockTypeModel on to our model
        $blockType->matrixBlockType = $blockType->getBlockType();

        // Save the field layout content on to our model
        $layout = $blockType->getFieldLayout();
        $fields = array();
        foreach ($layout->getFields() as $field)
        {
            $fields[] = array(
                'tabId' => $field->tabId,
                'sortOrder' => $field->sortOrder,
                'field' => $field->getField()
            );
        }

        $blockType->fieldLayout = array(
            'tabs'   => $layout->getTabs(),
            'fields' => $fields
        );

        return $blockType;
    }

}

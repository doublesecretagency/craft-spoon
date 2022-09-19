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

namespace doublesecretagency\spoon\controllers;

use Craft;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\models\FieldLayout;
use craft\web\Controller;
use doublesecretagency\spoon\models\BlockType as BlockTypeModel;
use doublesecretagency\spoon\records\BlockType as BlockTypeRecord;
use doublesecretagency\spoon\services\BlockTypes;
use doublesecretagency\spoon\Spoon;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Group Settings Controller
 * @since 4.0.0
 */
class GroupSettingsController extends Controller
{

    /**
     * Returns the fld HTML for the group settings modal.
     *
     * @return Response
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws BadRequestHttpException
     */
    public function actionModal(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        // Get the context
        $context = Craft::$app->getRequest()->getParam('context');

        // Get ID of Matrix field
        $fieldId = Craft::$app->getRequest()->getParam('fieldId');

        // Get block types of this Matrix field
        $types = Craft::$app->matrix->getBlockTypesByFieldId($fieldId);

        // Initialize set of block types
        $blockTypes = [];

        // Loop through block types
        foreach ($types as $blockType) {
            // Append each block type details to the array
            $blockTypes[] = [
                'id' => (int) $blockType->id,
                'name' => $blockType->name,
                'handle' => $blockType->handle
            ];
        }

        /**
         * ------------------------------------------------ //
         * Can this snippet be refactored?
         */
        // Initialize current configuration
        $config = [];
        // Get block types (the old-fashioned way)
        $spoonedBlockTypes = Spoon::$plugin->blockTypes->getByContext($context, 'groupName', false, $fieldId);
        // Loop through groups
        foreach ($spoonedBlockTypes as $groupName => $group) {
            // Initialize array of selected IDs
            $selected = [];
            // Append each block type ID to array of selected IDs
            foreach ($group as $blockType) {
                $selected[] = (int) $blockType->matrixBlockTypeId;
            }
            // Append each group details to the array
            $config[] = [
                'name' => $groupName,
                'ids' => $selected
            ];
        }
        /**
         * ------------------------------------------------ //
         */

        // Compile the field layout designer HTML
        $fld = Craft::$app->getView()->renderTemplate('spoon/group-settings/modal', [
            'fieldId' => $fieldId,
            'context' => $context,
            'blockGroupsData' => [
                'config' => $config,
                'elements' => $blockTypes,
            ]
        ]);

        // Return compiled HTML
        return $this->asJson([
            'html' => $fld
        ]);
    }

    /**
     * Saves a set of Spoon block types.
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionSave(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        // Get request services
        $request = Craft::$app->getRequest();

        // Get context and Matrix field ID
        $context = (string) $request->getParam('context');
        $fieldId = (int) $request->getParam('fieldId');

        // Get config
        $config = $request->getParam('config');

        // JSON decode config
        $config = Json::decode($config);

        // Get any existing field layouts, so we don’t lose them
        $fieldLayoutIds = Spoon::$plugin->blockTypes->getFieldLayoutIds($context, $fieldId);

        // Remove all current block types by context
        Spoon::$plugin->blockTypes->deleteByContext($context, $fieldId);

        // Assume no errors to start
        $errors = 0;

        // Initialize group order
        $groupOrder = 0;

        // Loop through configuration
        foreach ($config as $group) {

            // Increment the group order
            $groupOrder++;

            // Initialize block type order (within each group)
            $blockTypeOrder = 0;

            // Loop through selected block types for this group
            foreach ($group['ids'] ?? [] as $blockTypeId) {

                // If no ID, skip
                if (!$blockTypeId) {
                    continue;
                }

                // Increment the block type order
                $blockTypeOrder++;

                // Create new block type model
                $model = new BlockTypeModel([
                    'fieldId' => $fieldId,
                    'matrixBlockTypeId' => $blockTypeId,
                    'fieldLayoutId' => $fieldLayoutIds[$blockTypeId] ?? null,
                    'groupName' => $group['name'] ?? 'missing',
                    'context' => $context,
                    'groupSortOrder' => $groupOrder,
                    'sortOrder' => $blockTypeOrder,
                    'uid' => StringHelper::UUID(),
                ]);

                // Attempt to save the block type
                if (!$this->_save($model)) {
                    $errors++;
                }

            }

        }

        // Return whether saving was successful (no errors)
        return $this->asJson([
            'success' => (!$errors)
        ]);

    }

    // ========================================================================= //

    /**
     * Attempt to save the block type configuration.
     *
     * @param BlockTypeModel $model
     * @return bool
     */
    private function _save(BlockTypeModel $model): bool
    {

        /*
         * Is it unnecessary to save to the DB explicitly,
         * because we are saving directly to project config
         * and that is automatically updating the DB?
         */

//        // If unable to save to the database, bail
//        if (!$this->_saveToDatabase($model)) {
//            return false;
//        }

        // If unable to save to project config, bail
        if (!$this->_saveToProjectConfig($model)) {
            return false;
        }

        // Return success
        return true;
    }

//    /**
//     * Save block type to the database.
//     *
//     * @param BlockTypeModel $model
//     * @return bool
//     */
//    private function _saveToDatabase(BlockTypeModel $model): bool
//    {
//        // Create the block type record
//        $record = new BlockTypeRecord($model);
//
//        // Copy model attributes to record
//        $record->fieldId = $model->fieldId;
//        $record->matrixBlockTypeId = $model->matrixBlockTypeId;
//        $record->fieldLayoutId = $model->fieldLayoutId;
//        $record->groupName = $model->groupName;
//        $record->context = $model->context;
//        $record->groupSortOrder = $model->groupSortOrder;
//        $record->sortOrder = $model->sortOrder;
//        $record->uid = $model->uid;
//
//        // Return success
//        return true;
//    }

    /**
     * Save block type to a project config file.
     *
     * @param BlockTypeModel $model
     * @return bool
     * @throws Exception
     * @throws \craft\errors\BusyResourceException
     * @throws \craft\errors\StaleResourceException
     * @throws \yii\base\ErrorException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\NotSupportedException
     * @throws \yii\web\ServerErrorHttpException
     */
    private function _saveToProjectConfig(BlockTypeModel $model): bool
    {
        // Handle any currently attached field layouts
        /** @var FieldLayout $fieldLayout */
        $fieldLayout = $model->getFieldLayout();

        // If a field layout was specified and valid
        if ($model->fieldLayoutId && $fieldLayout && $fieldLayout->uid) {

            // Get the field layout config
            $fieldLayoutConfig = $fieldLayout->getConfig();

            // Get the UID
            $layoutUid = $fieldLayout->uid;

            // Specify the field layout
            $fieldLayout = [
                $layoutUid => $fieldLayoutConfig
            ];

        } else {

            // No field layout
            $fieldLayout = null;

        }

        // Get path for project config file
        $path = BlockTypes::CONFIG_BLOCKTYPE_KEY.'.'.$model->uid;

        // Save it to the project config
        Craft::$app->getProjectConfig()->set($path, [
            'groupName' => $model->groupName,
            'groupSortOrder' => $model->groupSortOrder,
            'sortOrder' => $model->sortOrder,
            'context' => $model->context,
            'field' => $model->getField()->uid,
            'fieldLayout' => $fieldLayout,
            'matrixBlockType' => $model->getBlockType()->uid,
        ]);

        // Return success
        return true;
    }

}

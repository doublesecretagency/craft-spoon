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
use craft\fieldlayoutelements\CustomField;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\models\FieldLayout;
use craft\web\Controller;
use doublesecretagency\spoon\errors\BlockTypeNotFoundException;
use doublesecretagency\spoon\models\BlockType;
use doublesecretagency\spoon\records\BlockType as BlockTypeRecord;
use doublesecretagency\spoon\Spoon;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * BlockTypes Controller
 * @since 3.0.0
 */
class BlockTypesController extends Controller
{

    /**
     * Delete a set of Spoon block types for a given field and context.
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $context = (string) Craft::$app->getRequest()->getParam('context');
        $fieldId = (integer) Craft::$app->getRequest()->getParam('fieldId');

        if (!Spoon::$plugin->blockTypes->deleteByContext($context, $fieldId))
        {
            $this->asJson([
                'success' => false
            ]);
        }

        return $this->asJson([
            'success' => true
        ]);
    }

    /**
     * Saves a field layout for a given Spoon block type.
     *
     * @return Response|bool
     * @throws BlockTypeNotFoundException
     * @throws BadRequestHttpException
     */
    public function actionSaveFieldLayout(): Response|bool
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        // Get request service
        $request = Craft::$app->getRequest();

        // Get the fields service
        $fieldsService = Craft::$app->getFields();

        // Get context and Matrix field ID
        $matrixBlockTypeId = (int) $request->getParam('matrixBlockTypeId');
        $context = (string) $request->getParam('context');

        // Get config
        $config = $request->getParam('config');

        // JSON decode config
        $config = Json::decode($config);

        // Initialize layout config
        $layoutConfig = [
            'tabs' => []
        ];

        // Loop through Vue config
        foreach ($config as $tab) {

            // Initialize tab elements
            $elements = [];

            // Loop through field IDs
            foreach ($tab['ids'] as $fieldId) {

                // Get the specified field
                $field = $fieldsService->getFieldById($fieldId);

                // Add field to tab elements
                $elements[] = [
                    'elementCondition' => null,
                    'fieldUid' => $field->uid,
                    'instructions' => null,
                    'label' => null,
                    'required' => false,
                    'tip' => null,
                    'type' => CustomField::class,
                    'uid' => StringHelper::UUID(),
                    'userCondition' => null,
                    'warning' => null,
                    'width' => 100,
                ];

            }

            // Add tab to field layout
            $layoutConfig['tabs'][] = [
                'elements' => $elements,
                'name' => $tab['name'],
                'uid' => StringHelper::UUID(),
                'elementCondition' => null,
                'userCondition' => null,
            ];
        }

        // Get the existing block type record
        $blockType = BlockTypeRecord::findOne([
            'matrixBlockTypeId' => $matrixBlockTypeId,
            'context' => $context
        ]);

        // If no block type record, create one
        $blockType = $blockType ?? new BlockTypeRecord();

        // Configure the field layout
        $layout = FieldLayout::createFromConfig($layoutConfig);
        $layout->id = ($blockType->fieldLayoutId ?? null);
        $layout->type = BlockType::class;
        $layout->uid = StringHelper::UUID();
//        $layout->uid = key($data['fieldLayouts']);

        // Save the field layout
        $fieldsService->saveLayout($layout, false);

        // Update the corresponding block type record
        $blockType->fieldLayoutId = $layout->id;

        // Update the block type
        $blockType->save();

        // Return success
        return $this->asJson([
            'success' => true
        ]);

    }

}

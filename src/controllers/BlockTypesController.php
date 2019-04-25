<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2018 Angell & Co
 */

namespace angellco\spoon\controllers;

use angellco\spoon\models\BlockType;
use angellco\spoon\Spoon;

use Craft;
use craft\helpers\Db;
use craft\web\Controller;

/**
 * BlockTypes Controller
 *
 * @author    Angell & Co
 * @package   Spoon
 * @since     3.0.0
 */
class BlockTypesController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = false;

    // Public Methods
    // =========================================================================

    /**
     * Loads the edit page for the global context.
     *
     * @param array $variables
     *
     * @return \yii\web\Response
     */
    public function actionIndex($variables = [])
    {
        $variables['matrixFields'] = Spoon::$plugin->fields->getMatrixFields();

        $variables['globalSpoonedBlockTypes'] = Spoon::$plugin->blockTypes->getByContext('global', 'fieldId', true);

        // If Super Table is installed get all of the ST fields and store by child field context
        $superTablePlugin = \Craft::$app->plugins->getPlugin('super-table');
        if ($superTablePlugin && $variables['matrixFields']) {
            $superTableService = new \verbb\supertable\services\SuperTableService();

            foreach ($variables['matrixFields'] as $matrixField) {
                if (strpos($matrixField->context, 'superTableBlockType') === 0) {
                    $parts = explode(':', $matrixField->context);
                    if (isset($parts[1])) {

                        $superTableBlockTypeId = Db::idByUid('{{%supertableblocktypes}}', $parts[1]);

                        if ($superTableBlockTypeId) {
                            /** @var \verbb\supertable\models\SuperTableBlockTypeModel $superTableBlockType */
                            $superTableBlockType = $superTableService->getBlockTypeById($superTableBlockTypeId);

                            /** @var \verbb\supertable\fields\SuperTableField $superTableField */
                            $superTableField = \Craft::$app->fields->getFieldById($superTableBlockType->fieldId);

                            $variables['superTableFields'][$matrixField->context] = [
                                'kind' => 'Super Table',
                                'field' => $superTableField,
                                'child' => false
                            ];

                            // If the context of _this_ field is inside a Matrix block ... then we need to do more inception
                            if (strpos($superTableField->context, 'matrixBlockType') === 0) {
                                $nestedParts = explode(':', $superTableField->context);
                                if (isset($nestedParts[1])) {

                                    $matrixBlockTypeId = Db::idByUid('{{%matrixblocktypes}}', $nestedParts[1]);

                                    if ($matrixBlockTypeId) {
                                        /** @var craft\models\MatrixBlockType $matrixBlockType */
                                        $matrixBlockType = \Craft::$app->matrix->getBlockTypeById($matrixBlockTypeId);

                                        /** @var craft\fields\Matrix $globalField */
                                        $globalField = \Craft::$app->fields->getFieldById($matrixBlockType->fieldId);

                                        $variables['superTableFields'][$matrixField->context] = [
                                            'kind' => 'Matrix',
                                            'field' => $globalField,
                                            'child' => [
                                                'kind' => 'Super Table',
                                                'field' => $superTableField
                                            ]
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        Spoon::$plugin->loader->configurator('#spoon-global-context-table', 'global');

        return $this->renderTemplate('spoon/edit-global-context', $variables);
    }

    /**
     * Saves a Spoon block type
     *
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        // This will be an array of Tab Names with Block Type IDs.
        // The order in which they appear is the order in which they should also
        // be returned in eventually, so we will just rely on the id to describe this
        // and make sure each time we are referencing a context that already exists to
        // delete the rows matching that context before proceeding with the save.
        $blockTypesPostData = Craft::$app->getRequest()->getParam('spoonedBlockTypes');

        $context = (string)Craft::$app->getRequest()->getParam('context');
        $fieldId = (integer)Craft::$app->getRequest()->getParam('fieldId');

        // Get any existing field layouts so we don’t lose them
        $fieldLayoutIds = Spoon::$plugin->blockTypes->getFieldLayoutIds($context, $fieldId);


        // Remove all current block types by context
        Spoon::$plugin->blockTypes->deleteByContext($context, $fieldId);

        // Loop over the data and save new rows for each block type / group combo
        $errors = 0;
        if (is_array($blockTypesPostData))
        {
            foreach ($blockTypesPostData as $groupName => $blockTypeIds)
            {
                foreach ($blockTypeIds as $blockTypeId)
                {
                    $spoonedBlockType = new BlockType();
                    $spoonedBlockType->fieldId           = $fieldId;
                    $spoonedBlockType->matrixBlockTypeId = $blockTypeId;
                    $spoonedBlockType->fieldLayoutId     = isset($fieldLayoutIds[$blockTypeId]) ? $fieldLayoutIds[$blockTypeId] : null;
                    $spoonedBlockType->groupName         = urldecode($groupName);
                    $spoonedBlockType->context           = $context;

                    if (!Spoon::$plugin->blockTypes->save($spoonedBlockType))
                    {
                        $errors++;
                    }
                }
            }
        }

        if ($errors > 0)
        {
            return $this->asJson([
                'success' => false
            ]);
        }

        return $this->asJson([
            'success' => true
        ]);
    }

    /**
     * Delete a set of Spoon block types for a given field and context
     *
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDelete()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $context = (string)Craft::$app->getRequest()->getParam('context');
        $fieldId = (integer)Craft::$app->getRequest()->getParam('fieldId');

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
     * Saves a field layout for a given Spoon block type
     *
     * @return bool|\yii\web\Response
     * @throws \angellco\spoon\errors\BlockTypeNotFoundException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveFieldLayout()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $spoonedBlockTypeId = Craft::$app->getRequest()->getParam('spoonedBlockTypeId');
        $blockTypeFieldLayouts = Craft::$app->getRequest()->getParam('blockTypeFieldLayouts');

        if ($spoonedBlockTypeId)
        {
            if (!$spoonedBlockType = Spoon::$plugin->blockTypes->getById($spoonedBlockTypeId)) {
                return false;
            }
        } else
        {
            return false;
        }

        // Set the field layout on the model
        $postedFieldLayout = Craft::$app->getRequest()->getParam('blockTypeFieldLayouts');

        // Check if we have one
        if ($postedFieldLayout) {
            $assembledLayout = Craft::$app->fields->assembleLayout($postedFieldLayout);
            $spoonedBlockType->setFieldLayout($assembledLayout);

            // Save it
            if (!Spoon::$plugin->blockTypes->saveFieldLayout($spoonedBlockType)) {
                return $this->asJson([
                    'success' => false
                ]);
            }
        } else if ($spoonedBlockType->fieldLayoutId) {

            // We don’t have a new field layout, so remove the old one if there is one
            $oldFieldLayoutId = $spoonedBlockType->fieldLayoutId;
            $spoonedBlockType->fieldLayoutId = null;
            if (!Spoon::$plugin->blockTypes->save($spoonedBlockType) || !Craft::$app->fields->deleteLayoutById($oldFieldLayoutId)) {
                return $this->asJson([
                    'success' => false
                ]);
            }
        }

        return $this->asJson([
            'success' => true
        ]);
    }

}

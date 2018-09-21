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
use angellco\spoon\records\BlockType as BlockTypeRecord;
use angellco\spoon\Spoon;

use Craft;
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
        $assembledLayout = Craft::$app->fields->assembleLayout($postedFieldLayout);
        $spoonedBlockType->setFieldLayout($assembledLayout);

        // Save it
        if (!Spoon::$plugin->blockTypes->saveFieldLayout($spoonedBlockType))
        {
            return $this->asJson([
                'success' => false
            ]);
        }

        return $this->asJson([
            'success' => true
        ]);

    }

}

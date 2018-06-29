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
use craft\web\Controller;

/**
 * BlockTypes Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
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

        $context = Craft::$app->getRequest()->getParam('context');
        $fieldId = Craft::$app->getRequest()->getParam('fieldId');

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
                    $pimpedBlockType = new BlockType();
                    $pimpedBlockType->fieldId           = $fieldId;
                    $pimpedBlockType->matrixBlockTypeId = $blockTypeId;
                    $pimpedBlockType->fieldLayoutId     = isset($fieldLayoutIds[$blockTypeId]) ? $fieldLayoutIds[$blockTypeId] : null;
                    $pimpedBlockType->groupName         = urldecode($groupName);
                    $pimpedBlockType->context           = $context;

                    if (!Spoon::$plugin->blockTypes->save($pimpedBlockType))
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
     * Delete a set of spooned block types for a given field and context
     */
    public function actionDelete()
    {
//        $this->requirePostRequest();
//        $this->requireAjaxRequest();
//
//        $context = craft()->request->getPost('context');
//        $fieldId = craft()->request->getPost('fieldId');
//
//        if (craft()->pimpMyMatrix_blockTypes->deleteBlockTypesByContext($context, $fieldId))
//        {
//            $this->returnJson(array(
//                'success' => true
//            ));
//        }
//        else
//        {
//            $this->returnJson(array(
//                'success' => false
//            ));
//        }

    }


//    /**
//     * Saves a field layout for a given pimped block type
//     */
//    public function actionSaveFieldLayout()
//    {
//
//        $this->requirePostRequest();
//        $this->requireAjaxRequest();
//
//        $pimpedBlockTypeId = craft()->request->getPost('pimpedBlockTypeId');
//        $blockTypeFieldLayouts = craft()->request->getPost('blockTypeFieldLayouts');
//
//        if ($pimpedBlockTypeId)
//        {
//
//            $pimpedBlockTypeRecord = PimpMyMatrix_BlockTypeRecord::model()->findById($pimpedBlockTypeId);
//
//            if (!$pimpedBlockTypeRecord)
//            {
//                throw new Exception(Craft::t('No PimpMyMatrix block type exists with the ID “{id}”', array('id' => $pimpedBlockTypeId)));
//            }
//
//            $pimpedBlockType = PimpMyMatrix_BlockTypeModel::populateModel($pimpedBlockTypeRecord);
//
//        }
//        else
//        {
//            return false;
//        }
//
//        // Set the field layout on the model
//        $postedFieldLayout = craft()->request->getPost('blockTypeFieldLayouts', array());
//        $assembledLayout = craft()->fields->assembleLayout($postedFieldLayout, array());
//        $pimpedBlockType->setFieldLayout($assembledLayout);
//
//        // Save it
//        if (craft()->pimpMyMatrix_blockTypes->saveFieldLayout($pimpedBlockType))
//        {
//            $this->returnJson(array(
//                'success' => true
//            ));
//        }
//        else
//        {
//            $this->returnJson(array(
//                'success' => false
//            ));
//        }
//
//    }
}

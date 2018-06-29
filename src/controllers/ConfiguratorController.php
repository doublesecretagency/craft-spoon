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

use angellco\spoon\Spoon;

use Craft;
use craft\web\Controller;

/**
 * Configurator Controller
 *
 * @author    Angell & Co
 * @package   Spoon
 * @since     3.0.0
 */
class ConfiguratorController extends Controller
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

    public function actionGetHtml()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $fieldId = Craft::$app->getRequest()->getParam('fieldId');
        $field = Craft::$app->fields->getFieldById($fieldId);
        $blockTypes = Craft::$app->matrix->getBlockTypesByFieldId($fieldId);

        $blockTypeIds = [];
        foreach ($blockTypes as $blockType)
        {
            $blockTypeIds[] = $blockType->id;
        }

        $context = Craft::$app->getRequest()->getParam('context');

        $spoonedBlockTypes = Spoon::$plugin->blockTypes->getBlockTypesByContext($context, 'groupName', false, $fieldId);

        $fld = Craft::$app->view->renderTemplate('spoon/flds/configurator', array(
            'matrixField' => $field,
            'blockTypes' => $blockTypes,
            'blockTypeIds' => $blockTypeIds,
            'spoonedBlockTypes' => $spoonedBlockTypes
        ));

        return $this->asJson([
            'html' => $fld
        ]);
    }
}

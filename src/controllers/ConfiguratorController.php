<?php
/**
 * Spoon plugin for Craft CMS
 *
 * Bend the Matrix field with block groups, tabs, and more.
 *
 * @author    Double Secret Agency
 * @link      https://plugins.doublesecretagency.com/
 * @copyright Copyright (c) 2018, 2022 Double Secret Agency
 */

namespace doublesecretagency\spoon\controllers;

use Craft;
use craft\web\Controller;
use doublesecretagency\spoon\Spoon;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Configurator Controller
 * @since 3.0.0
 */
class ConfiguratorController extends Controller
{

    /**
     * Returns the html for the individual block type fld.
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws InvalidConfigException
     */
    public function actionGetFieldsHtml(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $element = Craft::$app->getRequest()->getParam('element');
        $matrixBlockTypeId = Craft::$app->getRequest()->getParam('matrixBlockTypeId');
        $context = Craft::$app->getRequest()->getParam('context');

        $spoonedBlockType = Spoon::$plugin->blockTypes->getBlockType($context, $element['id']);

        // Get the existing tabs
        $tabs = ($spoonedBlockType->fieldLayoutModel['tabs'] ?? []);

        // Initialize current configuration
        $config = [];

        // Loop through groups
        foreach ($tabs as $tab) {

            // Initialize array of selected IDs
            $selected = [];

            // Append each block type ID to array of selected IDs
            foreach ($tab->elements as $e) {
                $selected[] = (int) $e->field->id;
            }

            // Append each group details to the array
            $config[] = [
                'name' => $tab->name,
                'ids' => $selected
            ];

        }

        // Get all fields for this block type
        $blockFields = $spoonedBlockType->matrixBlockType->getCustomFields();

        // Initialize set of block fields
        $fields = [];

        // Loop through block field
        foreach ($blockFields as $field) {
            // Append each field details to the array
            $fields[] = [
                'id' => (int) $field->id,
                'name' => $field->name,
                'handle' => $field->handle
            ];
        }

        // Configure the field layout designer
        $fld = Craft::$app->view->renderTemplate('spoon/group-settings/block-tabs', [
            'element' => $element,
            'matrixBlockTypeId' => $matrixBlockTypeId,
            'context' => $context,
            'blockTabsData' => [
                'config' => $config,
                'elements' => $fields,
            ]
        ]);

        // Return field layout designer HTML
        return $this->asJson([
            'html' => $fld
        ]);
    }

}

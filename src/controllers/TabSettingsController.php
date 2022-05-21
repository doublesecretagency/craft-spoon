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
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Tab Settings Controller
 * @since 4.0.0
 */
class TabSettingsController extends Controller
{
//
//    /**
//     * Returns the fld HTML for the main configurator.
//     *
//     * @return Response
//     * @throws Exception
//     * @throws LoaderError
//     * @throws RuntimeError
//     * @throws SyntaxError
//     * @throws BadRequestHttpException
//     */
//    public function actionModal(): Response
//    {
//        $this->requirePostRequest();
//        $this->requireAcceptsJson();
//
//        // Get the context
////        $context = Craft::$app->getRequest()->getParam('context');
//        $context = 'global';
//
//        // Get ID of Matrix field
//        $fieldId = Craft::$app->getRequest()->getParam('fieldId');
//
//        // Get block types of this Matrix field
//        $types = Craft::$app->matrix->getBlockTypesByFieldId($fieldId);
//
//        // Initialize set of block types
//        $blockTypes = [];
//
//        // Loop through block types
//        foreach ($types as $blockType) {
//            // Append each block type details to the array
//            $blockTypes[] = [
//                'id' => (int) $blockType->id,
//                'name' => $blockType->name,
//                'handle' => $blockType->handle
//            ];
//        }
//
//        /**
//         * ------------------------------------------------ //
//         * Can this snippet be refactored?
//         */
//        // Initialize current configuration
//        $config = [];
//        // Get block types (the old-fashioned way)
//        $spoonedBlockTypes = Spoon::$plugin->blockTypes->getByContext($context, 'groupName', false, $fieldId);
//        // Loop through groups
//        foreach ($spoonedBlockTypes as $groupName => $group) {
//            // Initialize array of selected IDs
//            $selected = [];
//            // Append each block type ID to array of selected IDs
//            foreach ($group as $blockType) {
//                $selected[] = (int) $blockType->matrixBlockTypeId;
//            }
//            // Append each group details to the array
//            $config[] = [
//                'name' => $groupName,
//                'blockTypes' => $selected
//            ];
//        }
//        /**
//         * ------------------------------------------------ //
//         */
//
//        // Compile the field layout designer HTML
//        $fld = Craft::$app->getView()->renderTemplate('spoon/group-settings/modal', [
//            'fieldId' => $fieldId,
//            'context' => $context,
//            'blockGroupsData' => [
//                'config' => $config,
//                'blockTypes' => $blockTypes,
//            ]
//        ]);
//
//        // Return compiled HTML
//        return $this->asJson([
//            'html' => $fld
//        ]);
//    }

}

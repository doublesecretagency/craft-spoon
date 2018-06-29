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

use angellco\spoon\assetbundles\Spoon\SpoonConfigurator;
use angellco\spoon\Spoon;

use Craft;
use craft\base\Component;
use craft\helpers\Json;

/**
 * Loader methods
 *
 * @author    Angell & Co
 * @package   Spoon
 * @since     3.0.0
 */
class Loader extends Component
{

//    /**
//     * Loads the configurator and field manupulator code in all the
//     * core supported contexts as well as providing a hook for
//     * third-party contexts.
//     */
//    public function loader()
//    {
//
//        // Check the conditions are right to run
//        if ( craft()->request->isCpRequest() && craft()->userSession->isLoggedIn() && !craft()->request->isAjaxRequest() )
//        {
//
//            $segments = craft()->request->getSegments();
//
//            /**
//             * Work out the context for the block type groups configuration
//             */
//            // Entry types
//            if ( count($segments) == 5
//                && $segments[0] == 'settings'
//                && $segments[1] == 'sections'
//                && $segments[3] == 'entrytypes'
//                && $segments[4] != 'new'
//            )
//            {
//                $this->loadConfigurator('#fieldlayoutform', 'entrytype:'.$segments[4]);
//            }
//
//            // Category groups
//            if ( count($segments) == 3
//                && $segments[0] == 'settings'
//                && $segments[1] == 'categories'
//                && $segments[2] != 'new'
//            )
//            {
//                $this->loadConfigurator('#fieldlayoutform', 'categorygroup:'.$segments[2]);
//            }
//
//            // Global sets
//            if ( count($segments) == 3
//                && $segments[0] == 'settings'
//                && $segments[1] == 'globals'
//                && $segments[2] != 'new'
//            )
//            {
//                $this->loadConfigurator('#fieldlayoutform', 'globalset:'.$segments[2]);
//            }
//
//            // Users
//            if ( count($segments) == 3
//                && $segments[0] == 'settings'
//                && $segments[1] == 'users'
//                && $segments[2] == 'fields'
//            )
//            {
//                $this->loadConfigurator('#fieldlayoutform', 'users');
//            }
//
//            // Call a hook to allow plugins to add their own configurator
//            $hookedConfigurators = craft()->plugins->call('loadPimpMyMatrixConfigurator');
//            foreach ($hookedConfigurators as $configurator)
//            {
//                if (isset($configurator['container']) && isset($configurator['context']))
//                {
//                    $this->loadConfigurator($configurator['container'], $configurator['context']);
//                }
//            }
//
//            /**
//             * Work out the context for the Matrix field manipulation
//             */
//            // Global
//            $context = 'global';
//
//            // Entry types
//            if ( count($segments) == 3 && $segments[0] == 'entries' )
//            {
//
//                if ($segments[2] == 'new')
//                {
//                    $section = craft()->sections->getSectionByHandle($segments[1]);
//                    $sectionEntryTypes = $section->getEntryTypes();
//                    $entryType = ArrayHelper::getFirstValue($sectionEntryTypes);
//                }
//                else
//                {
//                    $entryId = explode('-',$segments[2])[0];
//                    $entry = craft()->entries->getEntryById($entryId);
//
//                    if ($entry)
//                    {
//                        $entryType = $entry->type;
//                    }
//                }
//
//                $context = 'entrytype:'.$entryType->id;
//
//            }
//            // Category groups
//            else if ( count($segments) == 3 && $segments[0] == 'categories' )
//            {
//                $group = craft()->categories->getGroupByHandle($segments[1]);
//                if ($group)
//                {
//                    $context = 'categorygroup:'.$group->id;
//                }
//            }
//            // Global sets
//            else if ( count($segments) == 2 && $segments[0] == 'globals' )
//            {
//                $set = craft()->globals->getSetByHandle($segments[1]);
//                if ($set)
//                {
//                    $context = 'globalset:'.$set->id;
//                }
//            }
//            // Users
//            else if ( (count($segments) == 1 && $segments[0] == 'myaccount') || (count($segments) == 2 && $segments[0] == 'users') )
//            {
//                $context = 'users';
//            }
//
//            // Call a hook to allow plugins to add their own field manipulators
//            $hookedFieldManipulators = craft()->plugins->call('loadPimpMyMatrixFieldManipulator');
//            foreach ($hookedFieldManipulators as $hookedContext)
//            {
//                if (is_string($hookedContext))
//                {
//                    $context = $hookedContext;
//                }
//            }
//
//            // Run the field manipulation code
//            $this->loadFieldManipulator($context);
//
//        }
//
//    }
//
    /**
     * Loads a Spoon.Configurator() for the correct context
     */
    public function configurator($container, $context)
    {
        $view = Craft::$app->getView();

        $view->registerAssetBundle(SpoonConfigurator::class);

        $settings = [
            'matrixFieldIds' => Spoon::$plugin->fields->getMatrixFieldIds(),
            'context' => $context
        ];

        $view->registerJs('new Spoon.Configurator("'.$container.'", '.Json::encode($settings, JSON_UNESCAPED_UNICODE).');');

    }

//    /**
//     * Loads a PimpMyMatrix.FieldManipulator() for the corrext context
//     */
//    public function loadFieldManipulator($context)
//    {
//
//        // Get global data
//        $globalPimpedBlockTypes = craft()->pimpMyMatrix_blockTypes->getBlockTypesByContext('global', 'context');
//
//        // Get all the data for the entrytype context regardless of entrytype id
//        $mainContext = explode(':', $context)[0];
//        $contextPimpedBlockTypes = craft()->pimpMyMatrix_blockTypes->getBlockTypesByContext($mainContext, 'context', true);
//
//        $pimpedBlockTypes = array_merge($globalPimpedBlockTypes, $contextPimpedBlockTypes);
//
//        if ($pimpedBlockTypes)
//        {
//            craft()->templates->includeCssResource('pimpmymatrix/css/pimpmymatrix.css');
//
//            // Set up the groups
//            craft()->templates->includeJsResource('pimpmymatrix/js/fieldmanipulator.js');
//            $settings = array(
//                'blockTypes' => $pimpedBlockTypes,
//                'context' => $context
//            );
//            craft()->templates->includeJs('new PimpMyMatrix.FieldManipulator('.JsonHelper::Encode($settings).');');
//        }
//
//    }
}
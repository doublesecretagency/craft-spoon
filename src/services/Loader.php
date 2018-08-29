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
use angellco\spoon\assetbundles\Spoon\SpoonFieldManipulator;
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

    /**
     * Loads the configurator and field manupulator code in all the
     * core supported contexts as well as providing a hook for
     * third-party contexts.
     */
    public function run()
    {

        // Check the conditions are right to run
        if ( Craft::$app->request->isCpRequest && !Craft::$app->request->getAcceptsJson())
        {

            $segments = Craft::$app->request->getSegments();

            /**
             * Work out the context for the block type groups configuration
             */
            // Entry types
            if ( count($segments) == 5
                && $segments[0] == 'settings'
                && $segments[1] == 'sections'
                && $segments[3] == 'entrytypes'
                && $segments[4] != 'new'
            )
            {
                $this->configurator('#fieldlayoutform', 'entrytype:'.$segments[4]);
            }

            // Category groups
            if ( count($segments) == 3
                && $segments[0] == 'settings'
                && $segments[1] == 'categories'
                && $segments[2] != 'new'
            )
            {
                $this->configurator('#fieldlayoutform', 'categorygroup:'.$segments[2]);
            }

            // Global sets
            if ( count($segments) == 3
                && $segments[0] == 'settings'
                && $segments[1] == 'globals'
                && $segments[2] != 'new'
            )
            {
                $this->configurator('#fieldlayoutform', 'globalset:'.$segments[2]);
            }

            // Users
            if ( count($segments) == 2
                && $segments[0] == 'settings'
                && $segments[1] == 'users'
            )
            {
                $this->configurator('#fieldlayoutform', 'users');
            }

            /**
             * Work out the context for the Matrix field manipulation
             */
            // Global
            $context = 'global';

            // Entry types
            if ( count($segments) >= 3 && $segments[0] == 'entries' )
            {

                if ($segments[2] == 'new')
                {
                    $section = Craft::$app->sections->getSectionByHandle($segments[1]);
                    $sectionEntryTypes = $section->getEntryTypes();
                    $entryType = reset($sectionEntryTypes);
                }
                else
                {
                    $entryId = explode('-',$segments[2])[0];
                    $entry = Craft::$app->entries->getEntryById($entryId);

                    if ($entry)
                    {
                        $entryType = $entry->type;
                    }
                }

                if (isset($entryType) && $entryType) {
                    $context = 'entrytype:'.$entryType->id;
                }

            }
            // Category groups
            else if ( count($segments) >= 3 && $segments[0] == 'categories' )
            {
                $group = Craft::$app->categories->getGroupByHandle($segments[1]);
                if ($group)
                {
                    $context = 'categorygroup:'.$group->id;
                }
            }
            // Global sets
            else if ( count($segments) >= 2 && $segments[0] == 'globals' )
            {
                $set = Craft::$app->globals->getSetByHandle(end($segments));
                if ($set)
                {
                    $context = 'globalset:'.$set->id;
                }
            }
            // Users
            else if ( (count($segments) == 1 && $segments[0] == 'myaccount') || (count($segments) == 2 && $segments[0] == 'users') )
            {
                $context = 'users';
            }

            // Run the field manipulation code
            $this->fieldManipulator($context);

        }

    }

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

    /**
     * Loads a Spoon.FieldManipulator() for the corrext context
     */
    public function fieldManipulator($context)
    {

        // Get global data
        $globalSpoonedBlockTypes = Spoon::$plugin->blockTypes->getByContext('global', 'context');

        // Get all the data for the entrytype context regardless of entrytype id
        $mainContext = explode(':', $context)[0];
        $contextSpoonedBlockTypes = Spoon::$plugin->blockTypes->getByContext($mainContext, 'context', true);

        $spoonedBlockTypes = array_merge($globalSpoonedBlockTypes, $contextSpoonedBlockTypes);

        if ($spoonedBlockTypes)
        {

            $view = Craft::$app->getView();

            $view->registerAssetBundle(SpoonFieldManipulator::class);

            $settings = [
                'blockTypes' => $spoonedBlockTypes,
                'context' => $context,
                'nestedSettingsHandles' => Spoon::$plugin->getSettings()->nestedSettings
            ];

            $view->registerJs('Spoon.fieldmanipulator = new Spoon.FieldManipulator('.Json::encode($settings, JSON_UNESCAPED_UNICODE).');');

        }

    }

}
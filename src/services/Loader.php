<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://plugins.doublesecretagency.com/
 * @copyright Copyright (c) 2018, 2022 Double Secret Agency
 */

namespace doublesecretagency\spoon\services;

use doublesecretagency\spoon\assetbundles\ConfiguratorAsset;
use doublesecretagency\spoon\assetbundles\FieldManipulatorAsset;
use doublesecretagency\spoon\Spoon;

use Craft;
use craft\base\Component;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\models\Section;
use yii\base\InvalidConfigException;

/**
 * Loader methods
 *
 * @package   Spoon
 * @since     3.0.0
 */
class Loader extends Component
{

    /**
     * Loads the configurator and field manipulator code in all the
     * core supported contexts as well as providing a hook for
     * third-party contexts.
     *
     * @throws InvalidConfigException
     */
    public function run()
    {
        $request = Craft::$app->getRequest();

        // Check the conditions are right to run
        if ($request->getIsCpRequest() && !$request->getAcceptsJson())
        {

            $segments = Craft::$app->request->getSegments();
            $entries = Craft::$app->getEntries();

            /**
             * Check third party plugin support
             */
            $commercePlugin = Craft::$app->plugins->getPlugin('commerce');
            $calendarPlugin = Craft::$app->plugins->getPlugin('calendar');

            /**
             * Work out the context for the block type groups configuration
             */
            // Entry types
            if (\count($segments) === 5
                && $segments[0] === 'settings'
                && $segments[1] === 'sections'
                && $segments[3] === 'entrytypes'
                && $segments[4] !== 'new'
            )
            {
                $uid = Db::uidById('{{%entrytypes}}', $segments[4]);
                $this->configurator('#fieldlayoutform', 'entrytype:'.$uid);
            }

            // Category groups
            if (\count($segments) === 3
                && $segments[0] === 'settings'
                && $segments[1] === 'categories'
                && $segments[2] !== 'new'
            )
            {
                $uid = Db::uidById('{{%categorygroups}}', $segments[2]);
                $this->configurator('#fieldlayoutform', 'categorygroup:'.$uid);
            }

            // Global sets
            if (\count($segments) === 3
                && $segments[0] === 'settings'
                && $segments[1] === 'globals'
                && $segments[2] !== 'new'
            )
            {
                $uid = Db::uidById('{{%globalsets}}', $segments[2]);
                $this->configurator('#fieldlayoutform', 'globalset:'.$uid);
            }

            // Users
            if (\count($segments) === 2
                && $segments[0] === 'settings'
                && $segments[1] === 'users'
            )
            {
                $this->configurator('#fieldlayoutform', 'users');
            }

            // Solpace Calendar
            if (\count($segments) === 3
                && $segments[0] === 'calendar'
                && $segments[1] === 'calendars'
                && $segments[2] !== 'new'
                && $calendarPlugin
            )
            {
                $calendarService = new \Solspace\Calendar\Services\CalendarsService();
                if ($calendarService) {
                    $calendar = $calendarService->getCalendarByHandle(end($segments));
                    if ($calendar) {
                        $uid = Db::uidById(\Solspace\Calendar\Records\CalendarRecord::TABLE, $calendar->id);
                        $this->configurator('#fieldlayoutform', 'calendar:'.$uid);
                    }
                }
            }

            // Commerce
            if (\count($segments) === 4
                && $segments[0] === 'commerce'
                && $segments[1] === 'settings'
                && $segments[2] === 'producttypes'
                && $segments[3] !== 'new'
                && $commercePlugin
            ) {
                $productTypesService = new \craft\commerce\services\ProductTypes();
                if ($productTypesService) {
                    $productType = $productTypesService->getProductTypeById($segments[3]);
                    if ($productType) {
                        $this->configurator('#fieldlayoutform', 'producttype:'.$productType->uid);
                    }
                }
            }

            /**
             * Work out the context for the Matrix field manipulation
             */
            // Global
            $context = 'global';
            $versioned = false;

            // Entry types
            if (\count($segments) >= 3 && $segments[0] === 'entries') {

                if ($segments[2] === 'new') {
                    /** @var Section $section */
                    $section = Craft::$app->getSections()->getSectionByHandle($segments[1]);
                    $sectionEntryTypes = $section->getEntryTypes();
                    $entryType = reset($sectionEntryTypes);
                } else {
                    $entryId = (integer)explode('-', $segments[2])[0];

                    // Check if we have a site handle in the URL
                    if (isset($segments[3])) {
                        // If we do, get the site and fetch the entry with it
                        $site = Craft::$app->getSites()->getSiteByHandle($segments[3]);
                        $entry = $entries->getEntryById($entryId, $site !== null ? $site->id : null);
                    } else {
                        $entry = $entries->getEntryById($entryId);
                    }

                    if ($entry)
                    {
                        $entryType = $entry->type;
                    }
                }

                $revisionId = $request->getParam('revisionId');
                if ($revisionId) {
                    $versioned = true;
                }

                if (isset($entryType) && $entryType) {
                    $context = 'entrytype:'.$entryType->uid;
                }

            }
            // Category groups
            else if (\count($segments) >= 3 && $segments[0] === 'categories')
            {
                $group = Craft::$app->getCategories()->getGroupByHandle($segments[1]);
                if ($group)
                {
                    $context = 'categorygroup:'.$group->uid;
                }
            }
            // Global sets
            else if (\count($segments) >= 2 && $segments[0] === 'globals')
            {
                $set = Craft::$app->getGlobals()->getSetByHandle(end($segments));
                if ($set)
                {
                    $context = 'globalset:'.$set->uid;
                }
            }
            // Users
            else if ((\count($segments) === 1 && $segments[0] === 'myaccount') || (\count($segments) == 2 && $segments[0] === 'users'))
            {
                $context = 'users';
            }
            // Solspace Calendar
            else if (\count($segments) >= 4
                && $segments[0] === 'calendar'
                && $segments[1] === 'events'
                && $calendarPlugin
            ) {

                if ($segments[2] === 'new') {
                    $calendarService = new \Solspace\Calendar\Services\CalendarsService();
                    $calendar = $calendarService->getCalendarByHandle($segments[3]);
                    if ($calendar) {
                        $uid = Db::uidById(\Solspace\Calendar\Records\CalendarRecord::TABLE, $calendar->id);
                        $context = 'calendar:'.$uid;
                    }
                } else {
                    $calendarEventsService = new \Solspace\Calendar\Services\EventsService();
                    if ($calendarEventsService) {
                        $event = $calendarEventsService->getEventById($segments[2]);
                        if ($event) {
                            $uid = Db::uidById(\Solspace\Calendar\Records\CalendarRecord::TABLE, $event->getCalendar()->id);
                            $context = 'calendar:'.$uid;
                        }
                    }
                }
            }
            // Commerce
            else if (\count($segments) >= 3
                && $segments[0] === 'commerce'
                && $segments[1] === 'products'
                && $commercePlugin
            ) {
                $productTypesService = new \craft\commerce\services\ProductTypes();
                if ($productTypesService) {
                    $productType = $productTypesService->getProductTypeByHandle($segments[2]);
                    if ($productType) {
                        $context = 'producttype:'.$productType->uid;
                    }
                }
            }

            // Run the field manipulation code
            $this->fieldManipulator($context, $versioned);

        }

    }

    /**
     * Loads a Spoon.Configurator() for the correct context
     *
     * @param $container
     * @param $context
     *
     * @throws InvalidConfigException
     */
    public function configurator($container, $context)
    {

        $view = Craft::$app->getView();

        $view->registerAssetBundle(ConfiguratorAsset::class);

        $settings = [
            'matrixFieldIds' => Spoon::$plugin->fields->getMatrixFieldIds(),
            'context' => $context
        ];

        $view->registerJs('new Spoon.Configurator("'.$container.'", '.Json::encode($settings, JSON_UNESCAPED_UNICODE).');');

    }

    /**
     * Loads a Spoon.FieldManipulator() for the correct context
     *
     * @param      $context
     * @param bool $versioned
     *
     * @throws InvalidConfigException
     */
    public function fieldManipulator($context, $versioned = false)
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

            $view->registerAssetBundle(FieldManipulatorAsset::class);

            $translations = [];

            foreach ($spoonedBlockTypes as $spoonedBlockTypesInContext) {
                foreach ($spoonedBlockTypesInContext as $spoonedBlockType) {
                    $translations[] = $spoonedBlockType->groupName;
                    $translations[] = $spoonedBlockType->matrixBlockType->name;

                    if ($spoonedBlockType->fieldLayoutModel) {
                        foreach ($spoonedBlockType->fieldLayoutModel['tabs'] as $tab) {
                            $translations[] = $tab->name;
                        }
                    }

                }
            }

            $view->registerTranslations('site', $translations);

            $settings = [
                'blockTypes' => $spoonedBlockTypes,
                'context' => $context,
                'versioned' => $versioned,
                'nestedSettingsHandles' => Spoon::$plugin->getSettings()->nestedSettings
            ];

            $view->registerJs('if (typeof Craft.MatrixInput !== "undefined") { Spoon.fieldmanipulator = new Spoon.FieldManipulator('.Json::encode($settings, JSON_UNESCAPED_UNICODE).') };');
        }

    }

}

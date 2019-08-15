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

use angellco\spoon\assetbundles\ConfiguratorAsset;
use angellco\spoon\assetbundles\FieldManipulatorAsset;
use angellco\spoon\Spoon;

use Craft;
use craft\base\Component;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\models\Section;

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
     * Loads the configurator and field manipulator code in all the
     * core supported contexts as well as providing a hook for
     * third-party contexts.
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function run(): void
    {

        // Check the conditions are right to run
        if (Craft::$app->request->isCpRequest && !Craft::$app->request->getAcceptsJson())
        {

            $segments = Craft::$app->request->getSegments();

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
                        $this->configurator('#fieldlayoutform', 'calendar:'.$calendar->uid);
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
                    $section = Craft::$app->sections->getSectionByHandle($segments[1]);
                    $sectionEntryTypes = $section->getEntryTypes();
                    $entryType = reset($sectionEntryTypes);
                } else {
                    $entryId = (integer)explode('-', $segments[2])[0];

                    // Check if we have a site handle in the URL
                    if (isset($segments[3])) {
                        // If we do, get the site and fetch the entry with it
                        $site = Craft::$app->sites->getSiteByHandle($segments[3]);
                        $entry = Craft::$app->entries->getEntryById($entryId, $site !== null ? $site->id : null);
                    } else {
                        $entry = Craft::$app->entries->getEntryById($entryId);
                    }

                    if ($entry)
                    {
                        $entryType = $entry->type;
                    }
                }

                if (isset($segments[3]) && $segments[3] === 'versions') {
                    $versioned = true;
                }

                if (isset($entryType) && $entryType) {
                    $context = 'entrytype:'.$entryType->uid;
                }

            }
            // Category groups
            else if (\count($segments) >= 3 && $segments[0] === 'categories')
            {
                $group = Craft::$app->categories->getGroupByHandle($segments[1]);
                if ($group)
                {
                    $context = 'categorygroup:'.$group->uid;
                }
            }
            // Global sets
            else if (\count($segments) >= 2 && $segments[0] === 'globals')
            {
                $set = Craft::$app->globals->getSetByHandle(end($segments));
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
                        $context = 'calendar:'.$calendar->uid;
                    }
                } else {
                    $calendarEventsService = new \Solspace\Calendar\Services\EventsService();
                    if ($calendarEventsService) {
                        $event = $calendarEventsService->getEventById($segments[2]);
                        if ($event) {
                            $context = 'calendar:'.$event->getCalendar()->uid;
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
     * @throws \yii\base\InvalidConfigException
     */
    public function configurator($container, $context): void
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
     * @throws \yii\base\InvalidConfigException
     */
    public function fieldManipulator($context, $versioned = false): void
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

                    foreach ($spoonedBlockType->fieldLayoutModel['tabs'] as $tab) {
                        $translations[] = $tab->name;
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

<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2018 Angell & Co
 */

namespace angellco\spoon;

use angellco\spoon\models\Settings;
use angellco\spoon\services\Fields as FieldsService;
use angellco\spoon\services\BlockTypes as BlockTypesService;
use angellco\spoon\services\Loader as LoaderService;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Angell & Co
 * @package   Spoon
 * @since     3.0.0
 *
 * @property  FieldsService $fields
 * @property  BlockTypesService $blockTypes
 * @property  LoaderService $loader
 * @method    Settings getSettings()
 */
class Spoon extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Spoon::$plugin
     *
     * @var Spoon
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '3.0.0';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Spoon::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        if ($this->isInstalled && !Craft::$app->plugins->doesPluginRequireDatabaseUpdate($this))
        {
            $this->loader->run();
        }

//        // Register our site routes
//        Event::on(
//            UrlManager::class,
//            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
//            function (RegisterUrlRulesEvent $event) {
//                $event->rules['siteActionTrigger1'] = 'spoon/default';
//                $event->rules['siteActionTrigger2'] = 'spoon/block-types';
//            }
//        );
//
        // Register our CP routes
//        Event::on(
//            UrlManager::class,
//            UrlManager::EVENT_REGISTER_CP_URL_RULES,
//            function (RegisterUrlRulesEvent $event) {
//                $event->rules['spoon/getConfigurator'] = 'spoon/default/getConfigurator';
//                $event->rules['cpActionTrigger2'] = 'spoon/block-types/do-something';
//            }
//        );
//
//        // Do something after we're installed
//        Event::on(
//            Plugins::class,
//            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
//            function (PluginEvent $event) {
//                if ($event->plugin === $this) {
//                    // We were just installed
//                }
//            }
//        );


/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'spoon',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    /**
     * Loads the edit page for the global context.
     *
     * @return mixed|\yii\web\Response
     */
    public function getSettingsResponse()
    {
        $variables = [];

        $variables['matrixFields'] = $this->fields->getMatrixFields();

        $variables['globalSpoonedBlockTypes'] = $this->blockTypes->getByContext('global', 'fieldId', true);

        // If Super Table is installed get all of the ST fields and store by child field context
        $superTablePlugin = \Craft::$app->plugins->getPluginByPackageName('verbb/super-table');
        if ($superTablePlugin && $variables['matrixFields']) {
            $superTableService = new \verbb\supertable\services\SuperTableService();

            foreach ($variables['matrixFields'] as $matrixField) {
                if (strpos($matrixField->context, 'superTableBlockType') === 0) {
                    $parts = explode(':', $matrixField->context);
                    if (isset($parts[1])) {

                        /** @var \verbb\supertable\models\SuperTableBlockTypeModel $superTableBlockType */
                        $superTableBlockType = $superTableService->getBlockTypeById($parts[1]);

                        $variables['superTableFields'][$matrixField->context] = \Craft::$app->fields->getFieldById($superTableBlockType->fieldId);
                    }
                }
            }
        }

        $this->loader->configurator('#spoon-global-context-table', 'global');

        return \Craft::$app->controller->renderTemplate('spoon/_settings/edit-global-context', $variables);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

}

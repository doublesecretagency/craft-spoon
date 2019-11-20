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
use craft\events\RegisterUrlRulesEvent;
use craft\services\Plugins;

use craft\web\UrlManager;
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

    /**
     * @var bool Whether the plugin has its own section in the CP
     */
    public $hasCpSection = true;


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

        // Register CP URLs
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules['spoon'] = 'spoon/block-types/index';
            }
        );

        // Wait until all the plugins have loaded before running the loader
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_LOAD_PLUGINS,
            function() {
                if ($this->isInstalled && !Craft::$app->plugins->doesPluginRequireDatabaseUpdate($this)) {
                    $this->loader->run();
                }
            }
        );

        // Project config listeners
        Craft::$app->projectConfig
            ->onAdd($this->blockTypes::CONFIG_BLOCKTYPE_KEY.'.{uid}', [$this->blockTypes, 'handleChangedBlockType'])
            ->onUpdate($this->blockTypes::CONFIG_BLOCKTYPE_KEY.'.{uid}', [$this->blockTypes, 'handleChangedBlockType'])
            ->onRemove($this->blockTypes::CONFIG_BLOCKTYPE_KEY.'.{uid}', [$this->blockTypes, 'handleDeletedBlockType']);

        // Log on load for debugging
        Craft::info(
            Craft::t(
                'spoon',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
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

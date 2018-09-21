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
        $superTablePlugin = \Craft::$app->plugins->getPlugin('super-table');
        if ($superTablePlugin && $variables['matrixFields']) {
            $superTableService = new \verbb\supertable\services\SuperTableService();

            foreach ($variables['matrixFields'] as $matrixField) {
                if (strpos($matrixField->context, 'superTableBlockType') === 0) {
                    $parts = explode(':', $matrixField->context);
                    if (isset($parts[1])) {

                        /** @var \verbb\supertable\models\SuperTableBlockTypeModel $superTableBlockType */
                        $superTableBlockType = $superTableService->getBlockTypeById($parts[1]);

                        /** @var \verbb\supertable\fields\SuperTableField $superTableField */
                        $superTableField = \Craft::$app->fields->getFieldById($superTableBlockType->fieldId);

                        $variables['superTableFields'][$matrixField->context] = [
                            'kind' => 'Super Table',
                            'field' => $superTableField,
                            'child' => false
                        ];

                        // If the context of _this_ field is inside a Matrix block ... then we need to do more inception
                        if (strpos($superTableField->context, 'matrixBlockType') === 0) {
                            $nestedParts = explode(':', $superTableField->context);
                            if (isset($nestedParts[1])) {

                                /** @var craft\models\MatrixBlockType $matrixBlockType */
                                $matrixBlockType = \Craft::$app->matrix->getBlockTypeById((integer)$nestedParts[1]);

                                /** @var craft\fields\Matrix $globalField */
                                $globalField = \Craft::$app->fields->getFieldById($matrixBlockType->fieldId);

                                $variables['superTableFields'][$matrixField->context] = [
                                    'kind' => 'Matrix',
                                    'field' => $globalField,
                                    'child' => [
                                        'kind' => 'Super Table',
                                        'field' => $superTableField
                                    ]
                                ];

                            }
                        }

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

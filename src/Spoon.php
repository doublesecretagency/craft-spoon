<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://plugins.doublesecretagency.com/
 * @copyright Copyright (c) 2018, 2022 Double Secret Agency
 */

namespace doublesecretagency\spoon;

use doublesecretagency\spoon\base\PluginTrait;
use doublesecretagency\spoon\helpers\ProjectConfig as ProjectConfigHelper;
use doublesecretagency\spoon\models\Settings;
use doublesecretagency\spoon\services\BlockTypes;
use doublesecretagency\spoon\services\Fields;
use doublesecretagency\spoon\services\Loader;

use Craft;
use craft\base\Plugin;
use craft\events\RebuildConfigEvent;
use craft\helpers\Db;
use craft\services\Plugins;
use craft\services\ProjectConfig;

use verbb\supertable\fields\SuperTableField;
use verbb\supertable\models\SuperTableBlockTypeModel;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\web\Response;

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
 * @property BlockTypes     $blockTypes The block types component.
 * @property Fields         $fields     The fields component.
 * @property Loader         $loader     The loader component.
 * @property Response|mixed $settingsResponse
 * @method BlockTypes getBlockTypes() Returns the block types component.
 * @method Fields     getFields()     Returns the fields component.
 * @method Loader     getLoader()     Returns the loader component.
 * @method Settings   getSettings()   Returns the settings model.
 * @package   Spoon
 * @since     3.0.0
 */
class Spoon extends Plugin
{
    // Traits
    // =========================================================================

    use PluginTrait;

    // Public Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $schemaVersion = '3.5.0';

    /**
     * @inheritdoc
     */
    public $hasCpSettings = true;

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

        $this->_setPluginComponents();

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
            ->onAdd(BlockTypes::CONFIG_BLOCKTYPE_KEY.'.{uid}', [$this->getBlockTypes(), 'handleChangedBlockType'])
            ->onUpdate(BlockTypes::CONFIG_BLOCKTYPE_KEY.'.{uid}', [$this->getBlockTypes(), 'handleChangedBlockType'])
            ->onRemove(BlockTypes::CONFIG_BLOCKTYPE_KEY.'.{uid}', [$this->getBlockTypes(), 'handleDeletedBlockType']);

        // Project config rebuild listener
        Event::on(ProjectConfig::class, ProjectConfig::EVENT_REBUILD, function(RebuildConfigEvent $e) {
            $e->config[BlockTypes::CONFIG_BLOCKTYPE_KEY] = ProjectConfigHelper::rebuildProjectConfig();
        });

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

    /**
     * Loads the edit page for the global context.
     *
     * @return mixed|Response
     * @throws InvalidConfigException
     */
    public function getSettingsResponse()
    {
        $variables['matrixFields'] = $this->fields->getMatrixFields();

        $variables['globalSpoonedBlockTypes'] = $this->blockTypes->getByContext('global', 'fieldId', true);

        // If Super Table is installed get all of the ST fields and store by child field context
        $superTablePlugin = Craft::$app->plugins->getPlugin('super-table');
        if ($superTablePlugin && $variables['matrixFields']) {
            $superTableService = new \verbb\supertable\services\SuperTableService();

            foreach ($variables['matrixFields'] as $matrixField) {
                if (strpos($matrixField->context, 'superTableBlockType') === 0) {
                    $parts = explode(':', $matrixField->context);
                    if (isset($parts[1])) {

                        $superTableBlockTypeId = Db::idByUid('{{%supertableblocktypes}}', $parts[1]);

                        if ($superTableBlockTypeId) {
                            /** @var SuperTableBlockTypeModel $superTableBlockType */
                            $superTableBlockType = $superTableService->getBlockTypeById($superTableBlockTypeId);

                            /** @var SuperTableField $superTableField */
                            $superTableField = Craft::$app->fields->getFieldById($superTableBlockType->fieldId);

                            $variables['superTableFields'][$matrixField->context] = [
                                'kind' => 'Super Table',
                                'field' => $superTableField,
                                'child' => false
                            ];

                            // If the context of _this_ field is inside a Matrix block ... then we need to do more inception
                            if (strpos($superTableField->context, 'matrixBlockType') === 0) {
                                $nestedParts = explode(':', $superTableField->context);
                                if (isset($nestedParts[1])) {

                                    $matrixBlockTypeId = Db::idByUid('{{%matrixblocktypes}}', $nestedParts[1]);

                                    if ($matrixBlockTypeId) {
                                        /** @var craft\models\MatrixBlockType $matrixBlockType */
                                        $matrixBlockType = Craft::$app->matrix->getBlockTypeById($matrixBlockTypeId);

                                        /** @var craft\fields\Matrix $globalField */
                                        $globalField = Craft::$app->fields->getFieldById($matrixBlockType->fieldId);

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
            }
        }

        $this->getLoader()->configurator('#spoon-global-context-table', 'global');

        return Craft::$app->controller->renderTemplate('spoon/edit-global-context', $variables);
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

    /**
     * @inheritdoc
     */
    protected function afterUninstall()
    {
        // After uninstall drop project config keys
        $projectConfig = Craft::$app->getProjectConfig();
        $projectConfig->muteEvents = true;
        $projectConfig->remove(BlockTypes::CONFIG_BLOCKTYPE_KEY);
        $projectConfig->muteEvents = false;
    }

}

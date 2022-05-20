<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://plugins.doublesecretagency.com/
 * @copyright Copyright (c) 2018, 2022 Double Secret Agency
 */

namespace doublesecretagency\spoon\base;

use doublesecretagency\spoon\services\BlockTypes;
use doublesecretagency\spoon\services\Fields;
use doublesecretagency\spoon\services\Loader;
use doublesecretagency\spoon\Spoon;

/**
 * Trait PluginTrait
 *
 * @property-read BlockTypes $blockTypes The block types service
 * @property-read Fields $fields The fields service
 * @property-read Loader $loader The loader service
 * @package doublesecretagency\spoon\base
 */
trait PluginTrait
{
    // Static Properties
    // =========================================================================

    /**
     * @var Spoon
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * @return BlockTypes
     */
    public function getBlockTypes(): BlockTypes
    {
        return $this->get('blockTypes');
    }

    /**
     * @return Loader
     */
    public function getLoader(): Loader
    {
        return $this->get('loader');
    }

    /**
     * @return Fields
     */
    public function getFields(): Fields
    {
        return $this->get('fields');
    }

    // Private Methods
    // =========================================================================

    private function _setPluginComponents()
    {
        $this->setComponents([
            'blockTypes' => BlockTypes::class,
            'loader' => Loader::class,
            'fields' => Fields::class,
        ]);
    }
}

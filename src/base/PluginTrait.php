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

namespace doublesecretagency\spoon\base;

use doublesecretagency\spoon\Spoon;
use doublesecretagency\spoon\services\BlockTypes;
use doublesecretagency\spoon\services\Fields;
use doublesecretagency\spoon\services\Loader;

/**
 * Trait PluginTrait
 *
 * @property-read BlockTypes $blockTypes The block types service.
 * @property-read Fields $fields The fields service.
 * @property-read Loader $loader The loader service.
 */
trait PluginTrait
{

    /**
     * @var Spoon Self-referential plugin property.
     */
    public static Spoon $plugin;

    // ========================================================================= //

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

    // ========================================================================= //

    /**
     * Register services.
     */
    private function _setPluginComponents(): void
    {
        $this->setComponents([
            'blockTypes' => BlockTypes::class,
            'loader' => Loader::class,
            'fields' => Fields::class,
        ]);
    }

}

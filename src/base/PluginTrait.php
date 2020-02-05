<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2018 Angell & Co
 */

namespace angellco\spoon\base;

use angellco\spoon\services\BlockTypes;
use angellco\spoon\services\Fields;
use angellco\spoon\services\Loader;
use angellco\spoon\Spoon;

/**
 * Trait PluginTrait
 *
 * @property-read BlockTypes $blockTypes The block types service
 * @property-read Fields $fields The fields service
 * @property-read Loader $loader The loader service
 * @package angellco\spoon\base
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
<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2018 Angell & Co
 */

namespace angellco\spoon\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\View;

/**
 * ConfiguratorAsset AssetBundle
 *
 * @author    Angell & Co
 * @package   Spoon
 * @since     3.0.0
 */
class ConfiguratorAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * Initializes the bundle.
     */
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = "@angellco/spoon/assetbundles/dist";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/BlockTypeFieldLayoutDesigner.min.js',
            'js/GroupsDesigner.min.js',
            'js/Configurator.min.js'
        ];

        $this->css = [
            'css/main.min.css',
        ];

        parent::init();
    }


    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);

        if ($view instanceof View) {

            $view->registerTranslations('app', [
                "Group",
                "Rename",
                "Delete",
                "Make required",
                "Make not required",
                "Remove",
                "Give your tab a name.",
            ]);

            $view->registerTranslations('spoon', [
                "Edit field layout",
                "Give your group a name.",
                "Group block types",
                "Block type groups deleted.",
                "Block type groups saved.",
                "There was an unknown error saving some block type groups.",
            ]);
        }

    }

}

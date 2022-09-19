<?php
/**
 * Spoon plugin for Craft CMS
 *
 * Bend your Matrix fields with block groups & tabs.
 *
 * @author    Double Secret Agency
 * @link      https://plugins.doublesecretagency.com/
 * @copyright Copyright (c) 2018, 2022 Double Secret Agency
 */

namespace doublesecretagency\spoon\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\View;

/**
 * ConfiguratorAsset AssetBundle
 * @since 3.0.0
 */
class ConfiguratorAsset extends AssetBundle
{

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        // define the path that your publishable resources live
        $this->sourcePath = "@doublesecretagency/spoon/assetbundles/dist";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/GroupsDesigner.js',
            'js/Configurator.js',
            'js/vue/fld.js'
        ];

        $this->css = [
            'css/main.css',
            'css/fld.css',
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

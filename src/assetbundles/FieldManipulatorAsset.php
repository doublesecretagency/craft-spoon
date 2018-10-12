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
 * FieldManipulatorAsset AssetBundle
 *
 * @author    Angell & Co
 * @package   Spoon
 * @since     3.0.0
 */
class FieldManipulatorAsset extends AssetBundle
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
            'js/FieldManipulator.min.js',
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
                "Add a block",
            ]);

        }

    }

}

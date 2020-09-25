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
use craft\web\assets\matrix\MatrixAsset;
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
            MatrixAsset::class
        ];


        // TODO: hook up entire stack of flds to js window state so we can swap in the UI rendered version of the raw
        //       html around line 384 of FieldManipulator.js
//        $blockTypes = Spoon::$plugin->blockTypes->getByContext('global');
//
//        foreach ($blockTypes as $blockType) {
//
//            if ($blockType->fieldLayoutId) {
//                foreach ($blockType->getFieldLayout()->getTabs() as $tab) {
//                    Craft::dd($tab->elements);
//                }
//            }
//        }


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

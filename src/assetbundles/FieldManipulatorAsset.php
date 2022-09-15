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

namespace doublesecretagency\spoon\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\matrix\MatrixAsset;
use craft\web\View;

/**
 * FieldManipulatorAsset AssetBundle
 * @since 3.0.0
 */
class FieldManipulatorAsset extends AssetBundle
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
            'js/FieldManipulator.js',
        ];

        $this->css = [
            'css/main.css',
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

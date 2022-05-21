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

namespace doublesecretagency\spoon\models;

use craft\base\Model;
use craft\validators\ArrayValidator;

/**
 * Settings Model
 * @since 3.0.0
 */
class Settings extends Model
{

    /**
     * An array of Matrix field handles which should
     * use the nested settings menu display mode.
     *
     * @var array
     */
    public array $nestedSettings = [];

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            ['nestedSettings', ArrayValidator::class]
        ];
    }

}

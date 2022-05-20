<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://plugins.doublesecretagency.com/
 * @copyright Copyright (c) 2018, 2022 Double Secret Agency
 */

namespace doublesecretagency\spoon\models;

use doublesecretagency\spoon\Spoon;

use Craft;
use craft\base\Model;
use craft\validators\ArrayValidator;

/**
 * Settings Model
 *
 * @package   Spoon
 * @since     3.0.0
 */
class Settings extends Model
{

    // Public Properties
    // =========================================================================

    /**
     * An array of Matrix field handles that should use the nested settings menu
     * display mode
     *
     * @var array
     */
    public $nestedSettings = [];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['nestedSettings', ArrayValidator::class]
        ];
    }

}

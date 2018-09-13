<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2018 Angell & Co
 */

namespace angellco\spoon\models;

use angellco\spoon\Spoon;

use Craft;
use craft\base\Model;
use craft\validators\ArrayValidator;

/**
 * Settings Model
 *
 * @author    Angell & Co
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
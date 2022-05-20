<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://plugins.doublesecretagency.com/
 * @copyright Copyright (c) 2018, 2022 Double Secret Agency
 */

namespace doublesecretagency\spoon\errors;

use yii\base\Exception;

/**
 * Class BlockTypeNotFoundException
 *
 * @package   Spoon
 * @since     3.2.2
 */
class BlockTypeNotFoundException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Block Type not found';
    }
}

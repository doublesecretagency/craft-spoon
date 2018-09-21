<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2018 Angell & Co
 */

namespace angellco\spoon\errors;

use yii\base\Exception;

/**
 * Class BlockTypeNotFoundException
 *
 * @author    Angell & Co
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

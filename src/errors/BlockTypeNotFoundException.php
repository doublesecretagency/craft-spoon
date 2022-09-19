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

namespace doublesecretagency\spoon\errors;

use yii\base\Exception;

/**
 * Class BlockTypeNotFoundException
 * @since 3.2.2
 */
class BlockTypeNotFoundException extends Exception
{

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Block Type not found';
    }

}

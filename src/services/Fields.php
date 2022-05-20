<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://plugins.doublesecretagency.com/
 * @copyright Copyright (c) 2018, 2022 Double Secret Agency
 */

namespace doublesecretagency\spoon\services;

use doublesecretagency\spoon\Spoon;

use Craft;
use craft\base\Component;
use craft\db\Query;

/**
 * Fields
 *
 * @package   Spoon
 * @since     3.0.0
 */
class Fields extends Component
{

    // Private Properties
    // =========================================================================

    private $_matrixFieldIds;

    // Public Methods
    // =========================================================================

    /**
     * Returns an array of all the Matrix field ids
     * @return array
     */
    public function getMatrixFieldIds()
    {

        if (!$this->_matrixFieldIds)
        {
            $this->_matrixFieldIds = (new Query())
                ->select(['id'])
                ->from('{{%fields}}')
                ->where('type = :type', [':type' => 'craft\fields\Matrix'])
                ->column();
        }

        return $this->_matrixFieldIds;

    }

    /**
     * Returns an array of Matrix fields
     * @return array
     */
    public function getMatrixFields()
    {

        $return = [];

        foreach ($this->getMatrixFieldIds() as $fieldId)
        {
            $return[] = \Craft::$app->fields->getFieldById($fieldId);
        }

        return $return;

    }

}

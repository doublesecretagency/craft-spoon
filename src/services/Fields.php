<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2018 Angell & Co
 */

namespace angellco\spoon\services;

use angellco\spoon\Spoon;

use Craft;
use craft\base\Component;
use craft\db\Query;

/**
 * Fields
 *
 * @author    Angell & Co
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

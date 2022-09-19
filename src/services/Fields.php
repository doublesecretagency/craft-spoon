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

namespace doublesecretagency\spoon\services;

use Craft;
use craft\base\Component;
use craft\db\Query;

/**
 * Fields
 * @since 3.0.0
 */
class Fields extends Component
{

    /**
     * @var array Internal collection of Matrix field IDs.
     */
    private array $_matrixFieldIds = [];

    /**
     * Returns an array of all Matrix field IDs.
     *
     * @return array
     */
    public function getMatrixFieldIds(): array
    {
        // If array of IDs is empty
        if (!$this->_matrixFieldIds) {
            // Populate array of IDs
            $this->_matrixFieldIds = (new Query())
                ->select(['id'])
                ->from('{{%fields}}')
                ->where('type = :type', [':type' => 'craft\fields\Matrix'])
                ->column();
        }

        // Return array of Matrix field IDs
        return $this->_matrixFieldIds;
    }

    /**
     * Returns an array of Matrix fields.
     *
     * @return array
     */
    public function getMatrixFields(): array
    {
        // Initialize return array
        $return = [];

        // Loop through all Matrix field IDs
        foreach ($this->getMatrixFieldIds() as $fieldId) {
            // Append each Matrix field to the return array
            $return[] = Craft::$app->fields->getFieldById($fieldId);
        }

        // Return a set of Matrix fields
        return $return;
    }

}

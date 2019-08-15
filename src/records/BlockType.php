<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2018 Angell & Co
 */

namespace angellco\spoon\records;

use angellco\spoon\Spoon;

use Craft;
use craft\db\ActiveRecord;
use craft\records\Field;
use craft\records\FieldLayout;

use yii\db\ActiveQueryInterface;

/**
 * BlockType Record
 *
 * @property int    $id
 * @property int    $fieldId
 * @property int    $matrixBlockTypeId
 * @property int    $fieldLayoutId
 * @property string $groupName
 * @property string $context
 * @property \yii\db\ActiveQueryInterface $field
 * @property \yii\db\ActiveQueryInterface $fieldLayout
 *
 * @author    Angell & Co
 * @package   Spoon
 * @since     3.0.0
 */
class BlockType extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @return string the table name
     */
    public static function tableName()
    {
        return '{{%spoon_blocktypes}}';
    }

    /**
     * Returns the block type’s field.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getField(): ActiveQueryInterface
    {
        return $this->hasOne(Field::class, ['id' => 'fieldId']);
    }

    /**
     * Returns the block type’s fieldLayout.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getFieldLayout(): ActiveQueryInterface
    {
        return $this->hasOne(FieldLayout::class, ['id' => 'fieldLayoutId']);
    }

}

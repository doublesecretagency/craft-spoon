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

namespace doublesecretagency\spoon\records;

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
 * @property int    $groupSortOrder
 * @property int    $sortOrder
 *
 * @property ActiveQueryInterface $field
 * @property ActiveQueryInterface $fieldLayout
 *
 * @since 3.0.0
 */
class BlockType extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName(): string
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

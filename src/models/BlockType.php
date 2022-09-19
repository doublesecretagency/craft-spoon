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

namespace doublesecretagency\spoon\models;

use Craft;
use craft\base\FieldInterface;
use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\elements\MatrixBlock;
use craft\models\MatrixBlockType;

/**
 * BlockType Model
 * @since 3.0.0
 */
class BlockType extends Model
{

    /**
     * @var int|string|null ID The block ID. If unsaved, it will be in the format "newX".
     */
    public int|string|null $id = null;

    /**
     * @var int|null Field ID
     */
    public ?int $fieldId = null;

    /**
     * @var FieldInterface|null Field
     */
    public ?FieldInterface $field = null;

    /**
     * @var int|null Field layout ID
     */
    public ?int $fieldLayoutId = null;

    /**
     * @var array|null Field layout model
     */
    public ?array $fieldLayoutModel = null;

    /**
     * @var string|null Field handle
     */
    public ?string $fieldHandle = null;

    /**
     * @var int|null Matrix block type ID
     */
    public ?int $matrixBlockTypeId = null;

    /**
     * @var MatrixBlockType|null Matrix block type model
     */
    public ?MatrixBlockType $matrixBlockType = null;

    /**
     * @var string|null Group name
     */
    public ?string $groupName = null;

    /**
     * @var string|null Context
     */
    public ?string $context = null;

    /**
     * @var int
     */
    public int $groupSortOrder;

    /**
     * @var int
     */
    public int $sortOrder;

    /**
     * @var string|mixed
     */
    public mixed $uid = null;

    // ========================================================================= //

    /**
     * Use the block type name as the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getBlockType()->name;
    }

    /**
     * Returns the Field instance.
     *
     * @return FieldInterface|null
     */
    public function getField(): ?FieldInterface
    {
        if ($this->field) {
            return $this->field;
        }

        return Craft::$app->fields->getFieldById($this->fieldId);
    }

    /**
     * Returns the Matrix Block Type model.
     *
     * @return MatrixBlockType|null
     */
    public function getBlockType(): ?MatrixBlockType
    {
        if ($this->matrixBlockType) {
            return $this->matrixBlockType;
        }

        return Craft::$app->matrix->getBlockTypeById($this->matrixBlockTypeId);
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'fieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => MatrixBlock::class
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id', 'fieldId', 'matrixBlockTypeId', 'fieldLayoutId', 'groupSortOrder', 'sortOrder'], 'number', 'integerOnly' => true],
            [['fieldHandle', 'groupName', 'context'], 'string'],
//            ['matrixBlockType', MatrixBlockType::className()]
        ];
    }

}

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
use craft\behaviors\FieldLayoutBehavior;
use craft\elements\MatrixBlock;
use craft\models\MatrixBlockType;

/**
 * BlockType Model
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Angell & Co
 * @package   Spoon
 * @since     3.0.0
 */
class BlockType extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var int|string|null ID The block ID. If unsaved, it will be in the format "newX".
     */
    public $id;

    /**
     * @var int|null Field ID
     */
    public $fieldId;

    /**
     * @var int|null Field layout ID
     */
    public $fieldLayoutId;

    /**
     * @var mixed|null Field layout model
     */
    public $fieldLayoutModel;

    /**
     * @var string|null Field handle
     */
    public $fieldHandle;

    /**
     * @var int|null Matrix block type ID
     */
    public $matrixBlockTypeId;

    /**
     * @var MatrixBlockType|null Matrix block type model
     */
    public $matrixBlockType;

    /**
     * @var string|null Group name
     */
    public $groupName;

    /**
     * @var string|null Context
     */
    public $context;


    // Public Methods
    // =========================================================================

    /**
     * Use the block type name as the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->getBlockType()->name;
    }

    /**
     * Returns the Matrix Block Type model
     *
     * @return MatrixBlockType|null
     */
    public function getBlockType()
    {
        if ($this->matrixBlockType)
        {
            return $this->matrixBlockType;
        }
        else
        {
            return Craft::$app->matrix->getBlockTypeById($this->matrixBlockTypeId);
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'fieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => MatrixBlock::class
            ],
        ];
    }

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'fieldId', 'matrixBlockTypeId', 'fieldLayoutId'], 'number', 'integerOnly' => true],
            [['fieldHandle', 'groupName', 'context'], 'string'],
            ['matrixBlockType', MatrixBlockType::class]
        ];
    }

}

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

namespace doublesecretagency\spoon\migrations;

use craft\db\Migration;
use craft\records\FieldLayout as FieldLayoutRecord;
use doublesecretagency\spoon\models\BlockType;

/**
 * m190815_143313_UpdateFieldLayouts migration.
 */
class m190815_143313_UpdateFieldLayouts extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        $records = FieldLayoutRecord::findWithTrashed()->where(['type' => 'Spoon_BlockType'])->all();

        foreach ($records as $record) {
            $record->type = BlockType::class;
            $record->save(false);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190815_143313_UpdateFieldLayouts cannot be reverted.\n";
        return false;
    }

}

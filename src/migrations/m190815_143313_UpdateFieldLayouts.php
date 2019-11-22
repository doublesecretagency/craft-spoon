<?php

namespace angellco\spoon\migrations;

use angellco\spoon\models\BlockType;
use Craft;
use craft\db\Migration;
use craft\records\FieldLayout as FieldLayoutRecord;

/**
 * m190815_143313_UpdateFieldLayouts migration.
 */
class m190815_143313_UpdateFieldLayouts extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
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
    public function safeDown()
    {
        echo "m190815_143313_UpdateFieldLayouts cannot be reverted.\n";
        return false;
    }
}

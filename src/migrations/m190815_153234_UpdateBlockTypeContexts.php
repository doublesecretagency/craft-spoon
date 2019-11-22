<?php

namespace angellco\spoon\migrations;

use angellco\spoon\records\BlockType;
use Craft;
use craft\db\Migration;
use craft\helpers\Db;

/**
 * m190815_153234_UpdateBlockTypeContexts migration.
 */
class m190815_153234_UpdateBlockTypeContexts extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $records = BlockType::find()->all();
        foreach ($records as $record) {
            $parts = explode(':', $record->context);

            switch ($parts[0]) {

                case 'entrytype':
                    $uid = Db::uidById('{{%entrytypes}}', $parts[1]);
                    $record->context = 'entrytype:'.$uid;
                    $record->save(false);
                    break;

                case 'categorygroup':
                    $uid = Db::uidById('{{%categorygroups}}', $parts[1]);
                    $record->context = 'categorygroup:'.$uid;
                    $record->save(false);
                    break;

                case 'globalset':
                    $uid = Db::uidById('{{%globalsets}}', $parts[1]);
                    $record->context = 'globalset:'.$uid;
                    $record->save(false);
                    break;

                case 'calendar':
                    $uid = Db::uidById('{{%calendar_calendars}}', $parts[1]);
                    $record->context = 'calendar:'.$uid;
                    $record->save(false);
                    break;

                case 'producttype':
                    $uid = Db::uidById('{{%commerce_producttypes}}', $parts[1]);
                    $record->context = 'producttype:'.$uid;
                    $record->save(false);
                    break;

            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190815_153234_UpdateBlockTypeContexts cannot be reverted.\n";
        return false;
    }
}

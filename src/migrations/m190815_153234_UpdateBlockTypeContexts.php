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

namespace doublesecretagency\spoon\migrations;

use craft\db\Migration;
use craft\helpers\Db;
use doublesecretagency\spoon\records\BlockType;

/**
 * m190815_153234_UpdateBlockTypeContexts migration.
 */
class m190815_153234_UpdateBlockTypeContexts extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp(): void
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
    public function safeDown(): bool
    {
        echo "m190815_153234_UpdateBlockTypeContexts cannot be reverted.\n";
        return false;
    }

}

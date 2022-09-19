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

use Craft;
use craft\db\Migration;
use doublesecretagency\spoon\records\BlockType;

/**
 * m200204_132923_FixBlockTypeSorting migration.
 */
class m200204_132923_FixBlockTypeSorting extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {

        if (!$this->db->columnExists('{{%spoon_blocktypes}}', 'groupSortOrder')) {
            $this->addColumn('{{%spoon_blocktypes}}', 'groupSortOrder', $this->smallInteger()->unsigned()->after('context'));
        }

        if (!$this->db->columnExists('{{%spoon_blocktypes}}', 'sortOrder')) {
            $this->addColumn('{{%spoon_blocktypes}}', 'sortOrder', $this->smallInteger()->unsigned()->after('groupSortOrder'));
        }

        // Update sort orders to match current state of the DB
        $records = BlockType::find()->all();

        $groupedByContext = [];
        foreach ($records as $record) {
            $groupedByContext[$record['context']][$record['groupName']][] = $record;
        }

        foreach ($groupedByContext as $recordGroups) {

            $groupSortOrder = 1;
            foreach ($recordGroups as $recordGroup) {

                $sortOrder = 1;
                /** @var BlockType $record */
                foreach ($recordGroup as $record) {

                    $record->groupSortOrder = $groupSortOrder;
                    $record->sortOrder = $sortOrder;
                    $record->save(false);

                    $sortOrder++;
                }

                $groupSortOrder++;
            }

        }

        Craft::$app->projectConfig->rebuild();
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200204_132923_FixBlockTypeSorting cannot be reverted.\n";
        return false;
    }

}

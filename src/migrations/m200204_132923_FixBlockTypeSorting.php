<?php

namespace doublesecretagency\spoon\migrations;

use doublesecretagency\spoon\records\BlockType;
use Craft;
use craft\db\Migration;

/**
 * m200204_132923_FixBlockTypeSorting migration.
 */
class m200204_132923_FixBlockTypeSorting extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
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
    public function safeDown()
    {
        echo "m200204_132923_FixBlockTypeSorting cannot be reverted.\n";
        return false;
    }
}

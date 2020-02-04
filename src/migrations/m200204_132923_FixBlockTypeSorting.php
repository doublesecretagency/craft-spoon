<?php

namespace angellco\spoon\migrations;

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

        // TODO Work out the current state from the DB and update sort orders to match

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

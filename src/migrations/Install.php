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

use Craft;
use craft\db\Migration;

/**
 * Spoon Install Migration
 * @since 3.0.0
 */
class Install extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->removeTables();

        return true;
    }

    // ========================================================================= //

    /**
     * Creates the tables needed for the Records used by the plugin.
     *
     * @return bool
     */
    protected function createTables(): bool
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%spoon_blocktypes}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%spoon_blocktypes}}',
                [
                    'id' => $this->primaryKey(),
                    'fieldId' => $this->integer()->notNull(),
                    'matrixBlockTypeId' => $this->integer()->notNull(),
                    'fieldLayoutId' => $this->integer(),
                    'groupName' => $this->string(255)->notNull()->defaultValue(''),
                    'context' => $this->string(255)->notNull()->defaultValue(''),
                    'groupSortOrder' => $this->smallInteger()->unsigned(),
                    'sortOrder' => $this->smallInteger()->unsigned(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin.
     */
    protected function createIndexes(): void
    {
        // spoon_blocktypes table
        $this->createIndex(
            $this->db->getIndexName(
                '{{%spoon_blocktypes}}',
                'fieldId',
                false
            ),
            '{{%spoon_blocktypes}}',
            'fieldId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                '{{%spoon_blocktypes}}',
                'matrixBlockTypeId',
                false
            ),
            '{{%spoon_blocktypes}}',
            'matrixBlockTypeId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                '{{%spoon_blocktypes}}',
                'fieldLayoutId',
                false
            ),
            '{{%spoon_blocktypes}}',
            'fieldLayoutId',
            false
        );
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin.
     */
    protected function addForeignKeys(): void
    {
        // spoon_blocktypes table
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%spoon_blocktypes}}', 'fieldId'),
            '{{%spoon_blocktypes}}',
            'fieldId',
            '{{%fields}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%spoon_blocktypes}}', 'matrixBlockTypeId'),
            '{{%spoon_blocktypes}}',
            'matrixBlockTypeId',
            '{{%matrixblocktypes}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%spoon_blocktypes}}', 'fieldLayoutId'),
            '{{%spoon_blocktypes}}',
            'fieldLayoutId',
            '{{%fieldlayouts}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * Removes the tables needed for the Records used by the plugin.
     */
    protected function removeTables(): void
    {
        $this->dropTableIfExists('{{%spoon_blocktypes}}');
    }

}

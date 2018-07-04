<?php
/**
 * Spoon plugin for Craft CMS 3.x
 *
 * Enhance Matrix
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2018 Angell & Co
 */

namespace angellco\spoon\migrations;

use angellco\spoon\Spoon;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use craft\db\Query;

/**
 * Spoon Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    Angell & Co
 * @package   Spoon
 * @since     3.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {

        if ($this->_upgradeFromCraft2()) {
            return;
        }

        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        // spoon_blocktypes table
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
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
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

        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
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
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        // spoon_blocktypes table
        $this->dropTableIfExists('{{%spoon_blocktypes}}');
    }


    // Private Methods
    // =========================================================================

    /**
     * Upgrade from Craft 2
     *
     * @return bool
     */
    private function _upgradeFromCraft2()
    {
        // Fetch the old plugin row, if it was installed
        $row = (new Query())
            ->select(['id', 'settings'])
            ->from(['{{%plugins}}'])
            ->where(['in', 'handle', ['pimp-my-matrix', 'pimpmymatrix']])
            ->one();

        if (!$row) {
            return false;
        }

        // Delete the old row
        $this->delete('{{%plugins}}', ['id' => $row['id']]);

        // Rename the old table, the schema is identical
        $this->renameTable('{{%pimpmymatrix_blocktypes}}', '{{%spoon_blocktypes}}');

        return true;
    }

}

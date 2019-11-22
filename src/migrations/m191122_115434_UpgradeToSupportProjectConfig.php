<?php

namespace angellco\spoon\migrations;

use angellco\spoon\Spoon;
use Craft;
use craft\db\Migration;
use craft\helpers\App;

/**
 * m191122_115434_UpgradeToSupportProjectConfig migration.
 */
class m191122_115434_UpgradeToSupportProjectConfig extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        // Don't make the same config changes twice
        $schemaVersion = Craft::$app->projectConfig
            ->get('plugins.spoon.schemaVersion', true);

        if (version_compare($schemaVersion, '3.4.0', '<')) {
            // TODO Run the rebuild and commit it to project.yaml

        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m191122_115434_UpgradeToSupportProjectConfig cannot be reverted.\n";
        return false;
    }
}

<?php

namespace doublesecretagency\spoon\migrations;

use doublesecretagency\spoon\helpers\ProjectConfig as ProjectConfigHelper;
use doublesecretagency\spoon\services\BlockTypes;
use doublesecretagency\spoon\Spoon;
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

        $projectConfig = Craft::$app->getProjectConfig();

        // Don't make the same config changes twice
        $schemaVersion = $projectConfig->get('plugins.spoon.schemaVersion', true);

        if (version_compare($schemaVersion, '3.4.0', '<')) {
            // Run the rebuild and commit it to project.yaml
            $projectConfig->muteEvents = true;

            $projectConfig->set(BlockTypes::CONFIG_BLOCKTYPE_KEY, ProjectConfigHelper::rebuildProjectConfig());

            $projectConfig->muteEvents = false;
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

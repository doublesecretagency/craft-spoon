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
use doublesecretagency\spoon\helpers\ProjectConfig as ProjectConfigHelper;
use doublesecretagency\spoon\services\BlockTypes;

/**
 * m191122_115434_UpgradeToSupportProjectConfig migration.
 */
class m191122_115434_UpgradeToSupportProjectConfig extends Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp(): void
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
    public function safeDown(): bool
    {
        echo "m191122_115434_UpgradeToSupportProjectConfig cannot be reverted.\n";
        return false;
    }

}

<?php
namespace doublesecretagency\spoon\helpers;

use Craft;
use craft\db\Query;

class ProjectConfig
{

    /**
     * Rebuilds the project config data.
     *
     * @return array
     */
    public static function rebuildProjectConfig(): array
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $schemaVersion = $projectConfig->get('plugins.spoon.schemaVersion', true);
        $oldSchema = version_compare($schemaVersion, '3.4.0', '<');

        $fields = Craft::$app->getFields();
        $configData = [];

        $selectArray = [
            'b.uid',
            'b.fieldId',
            'b.matrixBlockTypeId',
            'b.fieldLayoutId',
            'b.groupName',
            'b.context',
            'b.uid',
            'f.uid AS fieldUid',
            'mbt.uid AS matrixBlockUid',
        ];

        if (!$oldSchema) {
            $selectArray[] = 'b.groupSortOrder';
            $selectArray[] = 'b.sortOrder';
        }

        $blockTypes = (new Query())
            ->select($selectArray)
            ->from(['{{%spoon_blocktypes}} b'])
            ->innerJoin('{{%fields}} f', '[[b.fieldId]] = [[f.id]]')
            ->innerJoin('{{%matrixblocktypes}} mbt', '[[b.matrixBlockTypeId]] = [[mbt.id]]')
            ->all();

        foreach ($blockTypes as $blockType) {

            $data = [
                'groupName' => $blockType['groupName'],
                'context' => $blockType['context'],
                'groupSortOrder' => $oldSchema ? null : $blockType['groupSortOrder'],
                'sortOrder' => $oldSchema ? null : $blockType['sortOrder'],
                'field' => $blockType['fieldUid'],
                'matrixBlockType' => $blockType['matrixBlockUid'],
            ];

            if ($blockType['fieldLayoutId']) {

                $layout = $fields->getLayoutById($blockType['fieldLayoutId']);

                if ($layout) {
                    $layoutConfig = $layout->getConfig();

                    $layoutUid = $layout->uid;

                    $data['fieldLayout'] = [
                        $layoutUid => $layoutConfig
                    ];
                }
            }

            $configData[$blockType['uid']] = $data;
        }

        return $configData;
    }
}

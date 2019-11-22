<?php
namespace angellco\spoon\helpers;

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
        $fields = Craft::$app->getFields();
        $configData = [];

        $blockTypes = (new Query())
            ->select([
                'b.uid',
                'b.fieldId',
                'b.matrixBlockTypeId',
                'b.fieldLayoutId',
                'b.groupName',
                'b.context',
                'b.uid',
                'f.uid AS fieldUid',
                'mbt.uid AS matrixBlockUid',
            ])
            ->from(['{{%spoon_blocktypes}} b'])
            ->innerJoin('{{%fields}} f', '[[b.fieldId]] = [[f.id]]')
            ->innerJoin('{{%matrixblocktypes}} mbt', '[[b.matrixBlockTypeId]] = [[mbt.id]]')
            ->all();

        foreach ($blockTypes as $blockType) {

            $data = [
                'groupName' => $blockType['groupName'],
                'context' => $blockType['context'],
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
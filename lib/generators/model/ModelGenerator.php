<?php

namespace extpoint\yii2\gii\generators\model;

use extpoint\yii2\gii\models\MigrationClass;
use extpoint\yii2\gii\models\MigrationMethods;
use extpoint\yii2\gii\models\ModelClass;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\gii\Generator;

class ModelGenerator extends Generator
{
    /**
     * @var ModelClass|null
     */
    public $oldModelClass;

    /**
     * @var ModelClass
     */
    public $modelClass;

    /**
     * One of value: create, update, none
     * @var string
     */
    public $migrateMode;

    public function getName()
    {
        return 'model';
    }

    public function requiredTemplates()
    {
        return ['meta', 'migrationCreate', 'migrationUpdate'];
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        // Create/update meta information
        (new CodeFile(
            $this->modelClass->metaClass->filePath,
            $this->render('meta.php', [
                'modelClass' => $this->modelClass,
            ])
        ))->save();
        \Yii::$app->session->addFlash('success', 'Мета информция модели ' . $this->modelClass->metaClass->name . ' обновлена');

        // Create model, if not exists
        if (!file_exists($this->modelClass->filePath)) {
            (new CodeFile(
                $this->modelClass->filePath,
                $this->render('model.php', [
                    'modelClass' => $this->modelClass,
                ])
            ))->save();
            \Yii::$app->session->addFlash('success', 'Добавлена модель ' . $this->modelClass->name);
        }

        // Create migration
        $migrationMethods = new MigrationMethods([
            'oldModelClass' => $this->oldModelClass,
            'modelClass' => $this->modelClass,
            'migrateMode' => $this->migrateMode,
        ]);
        if (!$migrationMethods->isEmpty()) {
            $migrationClass = new MigrationClass([
                'className' => $this->modelClass->moduleClass->namespace . '\\migrations\\' . $migrationMethods->generateName(),
            ]);
            (new CodeFile(
                $migrationClass->filePath,
                $this->render('migration.php', [
                    'migrationClass' => $migrationClass,
                    'migrationMethods' => $migrationMethods,
                ])
            ))->save();
            \Yii::$app->session->addFlash('success', 'Добавлена миграция ' . $migrationClass->name);
        }
    }


    public function exportRules(&$useClasses = [])
    {
        $types = [];
        $lengths = [];
        foreach ($this->modelClass->metaClass->meta as $metaItem) {
            if ($metaItem->getDbType() === 'pk') {
                continue;
            }
            if ($metaItem->required) {
                $types['required'][] = $metaItem->name;
            }

            switch ($metaItem->getParsedDbType()[0]) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $types['integer'][] = $metaItem->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $metaItem->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $metaItem->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $types['safe'][] = $metaItem->name;
                    break;
                default: // strings
                    if ($metaItem->getDbType()) {
                        if (count($metaItem->getParsedDbType()) > 1) {
                            $lengths[(int) $metaItem->getParsedDbType()[1]][] = $metaItem->name;
                        } else {
                            $types['string'][] = $metaItem->name;
                        }
                    } else {
                        $types['safe'][] = $metaItem->name;
                    }
            }
        }
        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }
        foreach ($lengths as $length => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
        }

        // Exist rules for foreign keys
        foreach ($this->modelClass->metaClass->relations as $relation) {
            if (!$relation->isHasOne) {
                continue;
            }

            $attribute = $relation->name;
            $refClassName = $relation->relationClass->name;
            $useClasses[] = $relation->relationClass->className;
            $targetAttributes = "'{$relation->selfKey}' => '{$relation->relationKey}'";

            $rules[] = "['$attribute', 'exist', 'skipOnError' => true, 'targetClass' => $refClassName::className(), 'targetAttribute' => [$targetAttributes]]";
        }

        return $rules;
    }


}
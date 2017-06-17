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
        return ['meta', 'migration', 'model'];
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

}
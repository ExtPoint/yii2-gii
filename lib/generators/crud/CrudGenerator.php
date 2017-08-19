<?php

namespace extpoint\yii2\gii\generators\crud;

use extpoint\yii2\gii\models\ControllerClass;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\ModuleClass;
use extpoint\yii2\gii\models\SearchModelClass;
use yii\gii\CodeFile;
use yii\gii\Generator;

class CrudGenerator extends Generator
{
    /**
     * @var ControllerClass
     */
    public $controllerClass;

    public function getName() {
        return 'crud';
    }

    public function requiredTemplates()
    {
        return ['controller', 'meta'];
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        // Create/update meta information
        (new CodeFile(
            $this->controllerClass->metaClass->filePath,
            $this->render('meta.php', [
                'controllerClass' => $this->controllerClass,
            ])
        ))->save();
        \Yii::$app->session->addFlash('success', 'Мета информция controller ' . $this->controllerClass->metaClass->name . ' обновлена');

        // Create controller, if not exists
        if (!file_exists($this->controllerClass->filePath)) {
            (new CodeFile(
                $this->controllerClass->filePath,
                $this->render('controller.php', [
                    'controllerClass' => $this->controllerClass,
                ])
            ))->save();
            \Yii::$app->session->addFlash('success', 'Добавлен controller ' . $this->controllerClass->name);
        }
    }

}
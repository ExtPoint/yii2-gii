<?php

namespace extpoint\yii2\gii\controllers;

use extpoint\yii2\gii\generators\model\ModelGenerator;
use extpoint\yii2\gii\generators\crud\CrudGenerator;
use extpoint\yii2\gii\generators\module\ModuleGenerator;
use extpoint\yii2\gii\helpers\GiiHelper;
use extpoint\yii2\base\Controller;
use yii\data\ArrayDataProvider;

class GiiController extends Controller
{
    public static function coreMenuItems() {
        return [
            'gii' => [
                'label' => 'Генератор кода',
                'url' => ['/gii/gii/index'],
                'urlRule' => 'admin/gii',
                'order' => 500,
                'items' => [
                    [
                        'label' => 'Модель',
                        'url' => ['/gii/gii/model'],
                        'urlRule' => 'admin/gii/model',
                    ],
                    [
                        'label' => 'CRUD',
                        'url' => ['/gii/gii/crud'],
                        'urlRule' => 'admin/gii/crud',
                    ]
                ]
            ],
        ];
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->post('create-module') === '') {
            $moduleId = \Yii::$app->request->post('moduleId');
            if ($moduleId) {
                (new ModuleGenerator([
                    'moduleId' => $moduleId,
                ]))->generate();
            }
        }

        $modelDataProvider = new ArrayDataProvider([
            'allModels' => GiiHelper::getModels(),
        ]);

        return $this->render('index', [
            'modelDataProvider' => $modelDataProvider,
        ]);
    }

    public function actionModel($moduleId = null, $modelName = null)
    {
        if (\Yii::$app->request->isPost) {
            $moduleId = \Yii::$app->request->post('moduleId');
            $modelName = \Yii::$app->request->post('modelName');

            // Check to create module
            if ($moduleId && !GiiHelper::isModuleExists($moduleId)) {
                (new ModuleGenerator([
                    'moduleId' => $moduleId,
                ]))->generate();
            }

            // Update model
            if ($moduleId && $modelName) {
                (new ModelGenerator([
                    'moduleId' => $moduleId,
                    'modelName' => $modelName,
                    'tableName' => \Yii::$app->request->post('tableName'),
                    'meta' => \Yii::$app->request->post('meta'),
                    'relations' => \Yii::$app->request->post('relations'),
                ]))->generate();

                return $this->redirect(['model', 'moduleId' => $moduleId, 'modelName' => $modelName]);
            }
        }

        return $this->render('model', [
            'initialValues' => [
                'moduleId' => $moduleId,
                'modelName' => $modelName,
            ],
        ]);
    }

    public function actionCrud($moduleId = null, $modelName = null)
    {
        $modelClassName = GiiHelper::getModel($moduleId, $modelName)['className'];
        $initialValues = [
            'modelClassName' => $modelClassName,
            'moduleId' => $moduleId,
            'createActionIndex' => true,
            'withSearch' => true,
            'withDelete' => true,
            'createActionCreate' => true,
            'createActionUpdate' => true,
            'createActionView' => true,
        ];

        if (\Yii::$app->request->isPost) {
            $modelClassName = \Yii::$app->request->post('modelClassName');
            $moduleId = \Yii::$app->request->post('moduleId');
            $name = \Yii::$app->request->post('name');
            $modelName = GiiHelper::getModelByClass($modelClassName)['name'];
            $initialValues = \Yii::$app->request->post();
            unset($initialValues['_csrf']);

            // Check to create module
            if ($moduleId && !GiiHelper::isModuleExists($moduleId)) {
                (new ModuleGenerator([
                    'moduleId' => $moduleId,
                ]))->generate();
            }

            // Create CRUD
            if ($moduleId && $name) {
                (new CrudGenerator($initialValues))->generate();
                return $this->redirect(['crud', 'moduleId' => $moduleId, 'modelName' => $modelName]);
            }
        }

        return $this->render('crud', [
            'initialValues' => $initialValues,
        ]);
    }

}

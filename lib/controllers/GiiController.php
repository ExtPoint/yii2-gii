<?php

namespace extpoint\yii2\gii\controllers;

use extpoint\yii2\gii\generators\model\ModelGenerator;
use extpoint\yii2\gii\generators\crud\CrudGenerator;
use extpoint\yii2\gii\generators\module\ModuleGenerator;
use extpoint\yii2\base\Controller;
use extpoint\yii2\gii\models\MetaItem;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\ModuleClass;
use extpoint\yii2\gii\models\Relation;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

class GiiController extends Controller
{
    public static function coreMenuItems() {
        return [
            'gii' => [
                'label' => 'Генератор кода',
                'url' => ['/gii/gii/index'],
                'urlRule' => 'admin/gii',
                'order' => 500,
                'roles' => 'admin',
                'visible' => YII_ENV_DEV,
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
        $modelDataProvider = new ArrayDataProvider([
            'allModels' => ModelClass::findAll(),
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
            if ($moduleId && !ModuleClass::findOne($moduleId)) {
                (new ModuleGenerator([
                    'moduleId' => $moduleId,
                ]))->generate();
            }

            // Update model
            if ($moduleId && $modelName) {
                $modelClass = new ModelClass([
                    'className' => ModelClass::idToClassName($moduleId, $modelName),
                    'tableName' => \Yii::$app->request->post('tableName'),
                ]);
                $modelClass->getMetaClass()->setMeta(
                    array_map(function($item) use ($modelClass) {
                        return new MetaItem(array_merge($item, [
                            'metaClass' => $modelClass->getMetaClass(),
                        ]));
                    }, \Yii::$app->request->post('meta', []))
                );
                $modelClass->getMetaClass()->setRelations(
                    array_map(function($item) {
                        $className = ArrayHelper::remove($item, 'relationModelClassName');
                        return new Relation(array_merge($item, [
                            'relationClass' => ModelClass::findOne($className),
                        ]));
                    }, \Yii::$app->request->post('relations', []))
                );

                (new ModelGenerator([
                    'oldModelClass' => ModelClass::findOne($modelClass->className),
                    'modelClass' => $modelClass,
                    'migrateMode' => \Yii::$app->request->post('migrateMode'),
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
        $modelClassName = ModelClass::findOne(ModelClass::idToClassName($moduleId, $modelName))->className;
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
            $modelName = ModelClass::findOne($modelClassName)->name;
            $initialValues = \Yii::$app->request->post();
            unset($initialValues['_csrf']);

            // Check to create module
            if ($moduleId && !ModuleClass::findOne($moduleId)) {
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

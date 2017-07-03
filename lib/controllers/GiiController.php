<?php

namespace extpoint\yii2\gii\controllers;

use extpoint\yii2\gii\generators\enum\EnumGenerator;
use extpoint\yii2\gii\generators\formModel\FormModelGenerator;
use extpoint\yii2\gii\generators\model\ModelGenerator;
use extpoint\yii2\gii\generators\crud\CrudGenerator;
use extpoint\yii2\gii\generators\module\ModuleGenerator;
use extpoint\yii2\base\Controller;
use extpoint\yii2\gii\GiiModule;
use extpoint\yii2\gii\models\EnumClass;
use extpoint\yii2\gii\models\EnumMetaItem;
use extpoint\yii2\gii\models\FormModelClass;
use extpoint\yii2\gii\models\MetaItem;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\ModuleClass;
use extpoint\yii2\gii\models\Relation;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

class GiiController extends Controller
{
    public static function coreMenuItems()
    {
        return [
            'gii' => [
                'label' => 'Генератор кода',
                'url' => ['/gii/gii/index'],
                'urlRule' => 'admin/gii',
                'order' => 500,
                'accessCheck' => [GiiModule::className(), 'accessCheck'],
                'visible' => YII_ENV_DEV,
                'items' => [
                    [
                        'label' => 'Модель',
                        'url' => ['/gii/gii/model'],
                        'urlRule' => 'admin/gii/model',
                    ],
                    [
                        'label' => 'Форма',
                        'url' => ['/gii/gii/form-model'],
                        'urlRule' => 'admin/gii/form-model',
                    ],
                    [
                        'label' => 'Enum',
                        'url' => ['/gii/gii/enum'],
                        'urlRule' => 'admin/gii/enum',
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
        $modules = [];
        foreach (ModelClass::findAll() as $modelClass) {
            $modules[$modelClass->moduleClass->id]['models'][] = $modelClass;
        }
        foreach (FormModelClass::findAll() as $formModelClass) {
            $modules[$formModelClass->moduleClass->id]['formModels'][] = $formModelClass;
        }
        foreach (EnumClass::findAll() as $enumClass) {
            $modules[$enumClass->moduleClass->id]['enums'][] = $enumClass;
        }

        ksort($modules);

        return $this->render('index', [
            'modules' => $modules,
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
                if (\Yii::$app->request->post('refresh')) {
                    $modelClass = ModelClass::findOne(ModelClass::idToClassName($moduleId, $modelName));
                    (new ModelGenerator([
                        'oldModelClass' => $modelClass,
                        'modelClass' => $modelClass,
                        'migrateMode' => 'none',
                    ]))->generate();

                    return $this->redirect(['index']);
                } else {
                    $modelClass = new ModelClass([
                        'className' => ModelClass::idToClassName($moduleId, $modelName),
                        'tableName' => \Yii::$app->request->post('tableName'),
                    ]);
                    $modelClass->getMetaClass()->setMeta(
                        array_map(function ($item) use ($modelClass) {
                            return new MetaItem(array_merge($item, [
                                'metaClass' => $modelClass->getMetaClass(),
                            ]));
                        }, \Yii::$app->request->post('meta', []))
                    );
                    $modelClass->getMetaClass()->setRelations(
                        array_map(function ($item) {
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
                }

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

    public function actionFormModel($moduleId = null, $formModelName = null)
    {
        if (\Yii::$app->request->isPost) {
            $moduleId = \Yii::$app->request->post('moduleId');
            $formModelName = \Yii::$app->request->post('formModelName');

            // Check to create module
            if ($moduleId && !ModuleClass::findOne($moduleId)) {
                (new ModuleGenerator([
                    'moduleId' => $moduleId,
                ]))->generate();
            }

            // Update form model
            if ($moduleId && $formModelName) {
                $formModelClass = new FormModelClass([
                    'className' => FormModelClass::idToClassName($moduleId, $formModelName),
                ]);
                $modelClass = ModelClass::findOne(\Yii::$app->request->post('modelClass'));

                if (\Yii::$app->request->post('refresh')) {
                    (new FormModelGenerator([
                        'formModelClass' => $formModelClass,
                        'modelClass' => $modelClass,
                    ]))->generate();

                    return $this->redirect(['index']);
                } else {
                    $formModelClass->getMetaClass()->setMeta(
                        array_map(function ($item) use ($formModelClass) {
                            return new MetaItem(array_merge($item, [
                                'metaClass' => $formModelClass->getMetaClass(),
                            ]));
                        }, \Yii::$app->request->post('meta', []))
                    );

                    (new FormModelGenerator([
                        'formModelClass' => $formModelClass,
                        'modelClass' => $modelClass,
                    ]))->generate();
                }

                return $this->redirect(['form-model', 'moduleId' => $moduleId, 'formModelName' => $formModelName]);
            }
        }

        return $this->render('form-model', [
            'initialValues' => [
                'moduleId' => $moduleId,
                'formModelName' => $formModelName,
            ],
        ]);
    }

    public function actionEnum($moduleId = null, $enumName = null)
    {
        if (\Yii::$app->request->isPost) {
            $moduleId = \Yii::$app->request->post('moduleId');
            $enumName = \Yii::$app->request->post('enumName');

            // Check to create module
            if ($moduleId && !ModuleClass::findOne($moduleId)) {
                (new ModuleGenerator([
                    'moduleId' => $moduleId,
                ]))->generate();
            }

            // Update enum
            if ($moduleId && $enumName) {
                $enumClass = new EnumClass([
                    'className' => EnumClass::idToClassName($moduleId, $enumName),
                ]);

                if (\Yii::$app->request->post('refresh')) {
                    (new EnumGenerator([
                        'enumClass' => $enumClass,
                    ]))->generate();

                    return $this->redirect(['index']);
                } else {
                    $enumClass->getMetaClass()->setMeta(
                        array_map(function ($item) use ($enumClass) {
                            return new EnumMetaItem(array_merge($item, [
                                'metaClass' => $enumClass->getMetaClass(),
                            ]));
                        }, \Yii::$app->request->post('meta', []))
                    );

                    (new EnumGenerator([
                        'enumClass' => $enumClass,
                    ]))->generate();
                }

                return $this->redirect(['enum', 'moduleId' => $moduleId, 'enumName' => $enumName]);
            }
        }

        return $this->render('enum', [
            'initialValues' => [
                'moduleId' => $moduleId,
                'enumName' => $enumName,
            ],
        ]);
    }

    public function actionCrud($moduleId = null, $modelName = null)
    {
        $initialValues = [
            'moduleId' => $moduleId,
            'createActionIndex' => true,
            'withSearch' => true,
            'withDelete' => true,
            'createActionCreate' => true,
            'createActionUpdate' => true,
            'createActionView' => true,
        ];

        if (\Yii::$app->request->isPost) {
            $moduleId = \Yii::$app->request->post('moduleId');
            $initialValues['moduleId'] = $moduleId;

            $modelClassName = \Yii::$app->request->post('modelClassName');
            $initialValues['modelClassName'] = $modelClassName;

            $name = \Yii::$app->request->post('name');
            $modelClass = ModelClass::findOne($modelClassName);
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
                return $this->redirect(['crud', 'moduleId' => $modelClass->moduleClass->id, 'modelName' => $modelClass->name]);
            }
        } else {
            $initialValues['modelClassName'] = ModelClass::findOne(ModelClass::idToClassName($moduleId, $modelName))->className;
        }

        return $this->render('crud', [
            'initialValues' => $initialValues,
        ]);
    }

}

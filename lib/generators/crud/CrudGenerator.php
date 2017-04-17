<?php

namespace extpoint\yii2\gii\generators\crud;

use extpoint\yii2\base\Model;
use extpoint\yii2\gii\models\ModelClass;
use yii\gii\CodeFile;
use yii\gii\Generator;
use yii\helpers\Inflector;

class CrudGenerator extends Generator
{
    public $modelClassName;
    public $moduleId;
    public $name;
    public $createActionIndex;
    public $withSearch;
    public $withDelete;
    public $createActionCreate;
    public $createActionUpdate;
    public $createActionView;
    public $title;
    public $url;
    public $roles;
    public $requestFields;

    public function getName() {
        return 'crud';
    }

    public function requiredTemplates()
    {
        return ['search', 'controller'];
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $model = ModelClass::findOne($this->modelClassName);
        $moduleDir = \Yii::getAlias('@app') . '/' . str_replace('.', '/', $this->moduleId);
        $moduleNamespace = 'app\\' . str_replace('.', '\\', $this->moduleId);
        $requestFields = preg_split('/[^\s-_]+/', $this->requestFields, -1, PREG_SPLIT_NO_EMPTY);
        $roles = preg_split('/[^\w\d@*-_]+/', $this->roles, -1, PREG_SPLIT_NO_EMPTY);

        $controllerPath = $moduleDir . '/controllers/' . ucfirst($this->name) . 'Controller.php';
        if (file_exists($controllerPath)) {
            \Yii::$app->session->addFlash('danger', 'Контроллер ' . ucfirst($this->name) . 'Controller уже существует!');
            return;
        }

        /** @var Model $modelClass */
        $modelClass = $this->modelClassName;
        (new CodeFile(
            $controllerPath,
            $this->render('controller.php', [
                'namespace' => $moduleNamespace . '\\controllers',
                'className' => ucfirst($this->name) . 'Controller',
                'routePrefix' => '/' . str_replace('.', '/', $this->moduleId) . '/' . Inflector::camel2id($this->name),
                'pkParam' => $modelClass::getRequestParamName(),
                'modelName' => $model['name'],
                'modelClassName' => $this->modelClassName,
                'searchModelName' => $model['name'] . 'Search',
                'searchModelClassName' => $moduleNamespace . '\\forms\\' . $model['name'] . 'Search',
                'createActionIndex' => $this->createActionIndex,
                'withSearch' => $this->withSearch,
                'withDelete' => $this->withDelete,
                'createActionCreate' => $this->createActionCreate,
                'createActionUpdate' => $this->createActionUpdate,
                'createActionView' => $this->createActionView,
                'title' => $this->title,
                'url' => $this->url,
                'requestFields' => $requestFields,
                'roles' => $roles,
            ])
        ))->save();
        \Yii::$app->session->addFlash('success', 'Создан контроллер ' . ucfirst($this->name) . 'Controller');

        $searchModelPath = $moduleDir . '/forms/' . $model['name'] . 'Search.php';
        if ($this->createActionIndex && $this->withSearch && !file_exists($searchModelPath)) {
            (new CodeFile(
                $moduleDir . '/forms/' . $model['name'] . 'Search.php',
                $this->render('search.php', [
                    'namespace' => $moduleNamespace . '\\forms',
                    'className' => $model['name'] . 'Search',
                    'parentModelName' => $model['name'],
                    'parentModelClassName' => $this->modelClassName,
                ])
            ))->save();
            \Yii::$app->session->addFlash('success', 'Создана модель для фильтра ' . $model['name'] . 'Search');
        }

        $templateNames = [];
        if ($this->createActionIndex) {
            $templateNames[] = 'index';
        }
        if ($this->createActionCreate || $this->createActionUpdate) {
            $templateNames[] = 'update';
        }
        if ($this->createActionView) {
            $templateNames[] = 'view';
        }
        if (count($templateNames) > 0) {
            foreach ($templateNames as $templateName) {
                (new CodeFile(
                    $moduleDir . '/views/' . Inflector::camel2id($this->name) . '/' . $templateName . '.php',
                    $this->render('views/' . $templateName . '.php', [
                        'modelName' => $model['name'],
                        'modelClassName' => $this->modelClassName,
                        'searchModelName' => $model['name'] . 'Search',
                        'searchModelClassName' => $moduleNamespace . '\\forms\\' . $model['name'] . 'Search',
                        'createActionIndex' => $this->createActionIndex,
                        'withSearch' => $this->withSearch,
                        'withDelete' => $this->withDelete,
                        'createActionCreate' => $this->createActionCreate,
                        'createActionUpdate' => $this->createActionUpdate,
                        'createActionView' => $this->createActionView,
                        'meta' => $model['meta'],
                        'requestFields' => $requestFields,
                    ])
                ))->save();
            }
            \Yii::$app->session->addFlash('success', 'Созданы представления index, update, view');
        }

        \Yii::$app->session->addFlash('info', 'Не забудьте добавить ' . ucfirst($this->name) . 'Controller::coreMenuItems() в ваше меню!');
    }

    public function getSearchRules() {
        $generator = new \yii\gii\generators\crud\Generator();
        $generator->modelClass = $this->modelClassName;
        return $generator->generateSearchRules();
    }

    public function getSearchConditions() {
        $generator = new \yii\gii\generators\crud\Generator();
        $generator->modelClass = $this->modelClassName;
        return $generator->generateSearchConditions();
    }

    public function getGridViewActions() {
        $actions = [];
        if ($this->createActionView) {
            $actions[] = 'view';
        }
        if ($this->createActionUpdate) {
            $actions[] = 'update';
        }
        if ($this->withDelete) {
            $actions[] = 'delete';
        }
        return $actions;
    }
}
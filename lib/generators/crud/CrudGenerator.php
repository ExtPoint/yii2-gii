<?php

namespace extpoint\yii2\gii\generators\crud;

use extpoint\yii2\gii\models\ControllerClass;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\SearchModelClass;
use yii\gii\CodeFile;
use yii\gii\Generator;

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
        $modelClass = ModelClass::findOne($this->modelClassName);
        $controllerClass = new ControllerClass([
            'className' => $modelClass->moduleClass->namespace . '\\controllers\\' . ucfirst($this->name) . 'Controller',
            'title' => $this->title,
            'url' => $this->url,
            'requestFields' => $this->requestFields,
            'roles' => $this->roles,
        ]);
        $searchModelClass = new SearchModelClass([
            'className' => $modelClass->moduleClass->namespace . '\\forms\\' . $modelClass->name . 'Search',
            'modelClass' => $modelClass,
        ]);

        // Check CRUD is already exists
        if (file_exists($controllerClass->filePath)) {
            \Yii::$app->session->addFlash('danger', 'Контроллер ' . $controllerClass->name . ' уже существует!');
            return;
        }

        // Create controller
        (new CodeFile(
            $controllerClass->filePath,
            $this->render('controller.php', [
                'modelClass' => $modelClass,
                'controllerClass' => $controllerClass,
                'searchModelClass' => $searchModelClass,
                'createActionIndex' => $this->createActionIndex,
                'withSearch' => $this->withSearch,
                'withDelete' => $this->withDelete,
                'createActionCreate' => $this->createActionCreate,
                'createActionUpdate' => $this->createActionUpdate,
                'createActionView' => $this->createActionView,
            ])
        ))->save();
        \Yii::$app->session->addFlash('success', 'Создан контроллер ' . ucfirst($this->name) . 'Controller');

        // Create search model
        if ($this->createActionIndex && $this->withSearch && !file_exists($searchModelClass->filePath)) {
            (new CodeFile(
                $searchModelClass->filePath,
                $this->render('search.php', [
                    'modelClass' => $modelClass,
                    'searchModelClass' => $searchModelClass,
                ])
            ))->save();
            \Yii::$app->session->addFlash('success', 'Создана модель поиска ' . $searchModelClass->name);
        }

        // Create views
        $templateNames = [
            'index' => $this->createActionIndex,
            'update' => $this->createActionCreate || $this->createActionUpdate,
            'view' => $this->createActionView,
        ];
        foreach ($templateNames as $templateName => $doCreate) {
            if (!$doCreate) {
                continue;
            }

            (new CodeFile(
                $modelClass->moduleClass->folderPath . '/views/' . $controllerClass->id . '/' . $templateName . '.php',
                $this->render('views/' . $templateName . '.php', [
                    'modelClass' => $modelClass,
                    'controllerClass' => $controllerClass,
                    'searchModelClass' => $searchModelClass,
                    'createActionIndex' => $this->createActionIndex,
                    'withSearch' => $this->withSearch,
                    'withDelete' => $this->withDelete,
                    'createActionCreate' => $this->createActionCreate,
                    'createActionUpdate' => $this->createActionUpdate,
                    'createActionView' => $this->createActionView,
                ])
            ))->save();

            \Yii::$app->session->addFlash('success', 'Создано представление ' . $templateName);
        }

        \Yii::$app->session->addFlash('info', 'Не забудьте добавить ' . $controllerClass->name . '::coreMenuItems() в ваше меню!');
    }

    public function getGridViewActions() {
        $actions = [];
        if ($this->createActionView) {
            $actions[] = 'view';
        }
        if ($this->createActionCreate || $this->createActionUpdate) {
            $actions[] = 'update';
        }
        if ($this->withDelete) {
            $actions[] = 'delete';
        }
        return $actions;
    }
}
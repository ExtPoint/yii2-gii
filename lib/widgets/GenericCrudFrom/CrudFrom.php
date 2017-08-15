<?php

namespace extpoint\yii2\gii\widgets\GenericCrudFrom;

use extpoint\yii2\base\Widget;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\ModuleClass;

class GenericCrudFrom extends Widget
{
    public $initialValues;

    public function init()
    {
        echo $this->renderReact([
            'initialValues' => !empty($this->initialValues) ? $this->initialValues : null,
            'csrfToken' => \Yii::$app->request->csrfToken,
            'modules' => array_map(function($moduleClass) {
                /** @type ModuleClass $moduleClass */
                return [
                    'id' => $moduleClass->id,
                    'className' => $moduleClass->className,
                ];
            }, ModuleClass::findAll()),
            'models' => array_map(function($modelClass) {
                /** @type ModelClass $modelClass */
                return [
                    'className' => $modelClass->className,
                    'name' => $modelClass->name,
                    'moduleId' => $modelClass->moduleClass->id,
                ];
            }, ModelClass::findAll()),
        ]);
    }


}
<?php

namespace extpoint\yii2\gii\widgets\CrudForm;

use extpoint\yii2\base\Widget;
use extpoint\yii2\gii\models\ControllerClass;
use extpoint\yii2\gii\models\FormModelClass;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\ModuleClass;

class CrudForm extends Widget
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
            'models' => ModelClass::findAll(),
            'formModels' => FormModelClass::findAll(),
            'controllers' => ControllerClass::findAll(),
        ]);
    }


}
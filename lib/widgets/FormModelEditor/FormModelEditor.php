<?php

namespace extpoint\yii2\gii\widgets\FormModelEditor;

use extpoint\yii2\base\Type;
use extpoint\yii2\base\Widget;
use extpoint\yii2\gii\models\FormModelClass;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\ModuleClass;

class FormModelEditor extends Widget
{
    public $initialValues;

    public function init()
    {
        echo $this->renderReact([
            'initialValues' => !empty($this->initialValues) ? $this->initialValues : null,
            'csrfToken' => \Yii::$app->request->csrfToken,
            'modules' => ModuleClass::findAll(),
            'models' => ModelClass::findAll(),
            'formModels' => FormModelClass::findAll(),
            'appTypes' => array_map(function($appType) {
                /** @type Type $appType */
                return [
                    'name' => $appType->name,
                    'title' => ucfirst($appType->name),
                    'fieldProps' => $appType->giiOptions()
                ];
            }, \Yii::$app->types->getTypes()),
        ]);
    }

}
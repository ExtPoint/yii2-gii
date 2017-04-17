<?php

namespace extpoint\yii2\gii\widgets\ModelEditor;

use extpoint\yii2\base\Type;
use extpoint\yii2\base\Widget;
use extpoint\yii2\gii\helpers\GiiHelper;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\ModuleClass;
use yii\helpers\ArrayHelper;

class ModelEditor extends Widget
{
    public $initialValues;

    public function init()
    {
        echo $this->renderReact([
            'initialValues' => !empty($this->initialValues) ? $this->initialValues : null,
            'csrfToken' => \Yii::$app->request->csrfToken,
            'modules' => ModuleClass::findAll(),
            'models' => ModelClass::findAll(),
            'tableNames' => GiiHelper::getTableNames(),
            'dbTypes' => GiiHelper::getDbTypes(),
            'appTypes' => array_map(function($appType) {
                /** @type Type $appType */
                return [
                    'name' => $appType->name,
                    'title' => ucfirst($appType->name),
                    'fieldProps' => $appType->getGiiFieldProps()
                ];
            }, \Yii::$app->types->getTypes()),
        ]);
    }


}
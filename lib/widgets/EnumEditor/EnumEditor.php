<?php

namespace extpoint\yii2\gii\widgets\EnumEditor;

use extpoint\yii2\base\Widget;
use extpoint\yii2\gii\models\EnumClass;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\ModuleClass;

class EnumEditor extends Widget
{
    public $initialValues;

    public function init()
    {
        echo $this->renderReact([
            'initialValues' => !empty($this->initialValues) ? $this->initialValues : null,
            'csrfToken' => \Yii::$app->request->csrfToken,
            'modules' => ModuleClass::findAll(),
            'enums' => EnumClass::findAll(),
        ]);
    }


}
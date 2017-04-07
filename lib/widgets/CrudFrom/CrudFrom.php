<?php

namespace extpoint\yii2\gii\widgets\CrudFrom;

use extpoint\yii2\base\Widget;
use extpoint\yii2\gii\helpers\GiiHelper;

class CrudFrom extends Widget
{
    public $initialValues;

    public function init()
    {
        echo $this->renderReact([
            'initialValues' => !empty($this->initialValues) ? $this->initialValues : null,
            'csrfToken' => \Yii::$app->request->csrfToken,
            'modules' => GiiHelper::getModules(),
            'models' => GiiHelper::getModels(),
        ]);
    }


}
<?php

namespace extpoint\yii2\gii\widgets\ModelEditor;

use extpoint\yii2\base\Widget;
use extpoint\yii2\gii\helpers\GiiHelper;

class ModelEditor extends Widget
{
    public $initialValues;

    public function init()
    {
        echo $this->renderReact([
            'initialValues' => !empty($this->initialValues) ? $this->initialValues : null,
            'csrfToken' => \Yii::$app->request->csrfToken,
            'modules' => GiiHelper::getModules(),
            'models' => GiiHelper::getModels(),
            'tableNames' => GiiHelper::getTableNames(),
            'dbTypes' => GiiHelper::getDbTypes(),
            'fieldWidgets' => GiiHelper::getFieldWidgets(),
            'formatters' => GiiHelper::getFormatters(),
        ]);
    }


}
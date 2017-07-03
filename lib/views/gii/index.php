<?php

namespace extpoint\yii2\views;

use extpoint\yii2\gii\models\EnumClass;
use extpoint\yii2\gii\models\FormModelClass;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\ModuleClass;
use extpoint\yii2\widgets\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $modules array */

?>

<?php
foreach ($modules as $moduleId => $items) {
    ?>
    <h3><?= $moduleId ?></h3>

    <div class="row">

        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?= Html::a(
                        '<span class="glyphicon glyphicon-plus"></span>',
                        ['model', 'moduleId' => $moduleId],
                        [
                            'class' => 'btn btn-xs btn-default',
                            'style' => 'position: absolute; top: 23px; right: 32px;',
                        ]
                    ) ?>
                    <?= GridView::widget([
                        'dataProvider' => new ArrayDataProvider(['allModels' => !empty($items['models']) ? $items['models'] : []]),
                        'emptyText' => '',
                        'columns' => [
                            [
                                'label' => 'Model',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    /** @type ModelClass $model */
                                    return Html::a($model->name, ['/gii/gii/model', 'moduleId' => $model->moduleClass->id, 'modelName' => $model->name]);
                                }
                            ],
                            'tableName',
                            [
                                'format' => 'raw',
                                'value' => function ($model) {
                                    /** @type ModelClass $model */
                                    return Html::a('<span class="glyphicon glyphicon-plus"></span> CRUD', ['/gii/gii/crud', 'moduleId' => $model->moduleClass->id, 'modelName' => $model->name]);
                                }
                            ]
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?= Html::a(
                        '<span class="glyphicon glyphicon-plus"></span>',
                        ['form-model', 'moduleId' => $moduleId],
                        [
                            'class' => 'btn btn-xs btn-default',
                            'style' => 'position: absolute; top: 23px; right: 32px;',
                        ]
                    ) ?>
                    <?= GridView::widget([
                        'dataProvider' => new ArrayDataProvider(['allModels' => !empty($items['formModels']) ? $items['formModels'] : []]),
                        'emptyText' => '',
                        'columns' => [
                            [
                                'label' => 'Form',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    /** @type FormModelClass $model */
                                    return Html::a($model->name, ['/gii/gii/form-model', 'moduleId' => $model->moduleClass->id, 'formModelName' => $model->name]);
                                }
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?= Html::a(
                        '<span class="glyphicon glyphicon-plus"></span>',
                        ['enum', 'moduleId' => $moduleId],
                        [
                            'class' => 'btn btn-xs btn-default',
                            'style' => 'position: absolute; top: 23px; right: 32px;',
                        ]
                    ) ?>
                    <?= GridView::widget([
                        'dataProvider' => new ArrayDataProvider(['allModels' => !empty($items['enums']) ? $items['enums'] : []]),
                        'emptyText' => '',
                        'columns' => [
                            [
                                'attribute' => 'Enum',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    /** @type EnumClass $model */
                                    return Html::a($model->name, ['/gii/gii/enum', 'moduleId' => $model->moduleClass->id, 'enumName' => $model->name]);
                                }
                            ]
                        ],
                    ]) ?>
                </div>
            </div>
        </div>

    </div>
<?php } ?>

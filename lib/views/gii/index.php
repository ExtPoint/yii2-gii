<?php

namespace extpoint\yii2\views;

use extpoint\yii2\gii\models\EnumClass;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\widgets\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $modelDataProvider ArrayDataProvider */
/* @var $enumDataProvider ArrayDataProvider */

?>

<h3>
    Models
    <?= Html::a(
        '<span class="glyphicon glyphicon-plus"></span> Add',
        ['model'],
        ['class' => 'btn btn-sm btn-success',]
    ) ?>
</h3>

<?= GridView::widget([
    'dataProvider' => $modelDataProvider,
    'columns' => [
        [
            'attribute' => 'name',
            'format' => 'raw',
            'value' => function($model) {
                /** @type ModelClass $model */
                return Html::a($model->name, ['/gii/gii/model', 'moduleId' => $model->moduleClass->id, 'modelName' => $model->name]);
            }
        ],
        'metaClass.name',
        'moduleClass.id',
        'tableName',
        [
            'format' => 'raw',
            'value' => function($model) {
                /** @type ModelClass $model */
                return Html::a('<span class="glyphicon glyphicon-plus"></span> CRUD', ['/gii/gii/crud', 'moduleId' => $model->moduleClass->id, 'modelName' => $model->name]);
            }
        ]
    ],
]) ?>

<h3>
    Enums
    <?= Html::a(
        '<span class="glyphicon glyphicon-plus"></span> Add',
        ['enum'],
        ['class' => 'btn btn-sm btn-success',]
    ) ?>
</h3>

<?= GridView::widget([
    'dataProvider' => $enumDataProvider,
    'columns' => [
        [
            'attribute' => 'name',
            'format' => 'raw',
            'value' => function($model) {
                /** @type EnumClass $model */
                return Html::a($model->name, ['/gii/gii/enum', 'moduleId' => $model->moduleClass->id, 'enumName' => $model->name]);
            }
        ],
        'moduleClass.id',
    ],
]) ?>


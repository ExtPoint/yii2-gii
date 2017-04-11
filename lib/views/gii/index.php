<?php

namespace extpoint\yii2\views;

use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $modelDataProvider ArrayDataProvider */

?>

<h3>Models</h3>
<?= GridView::widget([
    'dataProvider' => $modelDataProvider,
    'columns' => [
        'module',
        [
            'attribute' => 'name',
            'format' => 'raw',
            'value' => function($model) {
                return Html::a($model['name'], ['/gii/gii/model', 'moduleId' => $model['module'], 'modelName' => $model['name']]);
            }
        ],
        'metaName',
        'tableName',
        [
            'format' => 'raw',
            'value' => function($model) {
                return Html::a('<span class="glyphicon glyphicon-plus"></span> CRUD', ['/gii/gii/crud', 'moduleId' => $model['module'], 'modelName' => $model['name']]);
            }
        ]
    ],
]) ?>


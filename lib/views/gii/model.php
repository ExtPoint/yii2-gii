<?php

namespace extpoint\yii2\views;

use extpoint\yii2\gii\widgets\ModelEditor\ModelEditor;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $initialValues array */

?>

<div class="indent pull-right" style="margin-top: -67px;">
    <?= Html::a(
        '<span class="glyphicon glyphicon-plus"></span> Создать новую',
        ['model'],
        ['class' => 'btn btn-success',]
    ) ?>
</div>

<?= ModelEditor::widget([
    'initialValues' => $initialValues
]) ?>

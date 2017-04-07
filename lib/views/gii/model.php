<?php

namespace extpoint\yii2\views;

use extpoint\yii2\gii\widgets\ModelEditor\ModelEditor;

/* @var $this \yii\web\View */
/* @var $initialValues array */

?>

<?= ModelEditor::widget([
    'initialValues' => $initialValues
]) ?>

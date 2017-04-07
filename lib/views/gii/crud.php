<?php

namespace extpoint\yii2\views;

use extpoint\yii2\gii\widgets\CrudFrom\CrudFrom;

/* @var $this \yii\web\View */
/* @var $initialValues array */

?>

<?= CrudFrom::widget([
    'initialValues' => $initialValues
]) ?>

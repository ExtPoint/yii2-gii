<?php

namespace extpoint\yii2\views;

use extpoint\yii2\gii\widgets\CrudForm\CrudForm;

/* @var $this \yii\web\View */
/* @var $initialValues array */

?>

<?= CrudForm::widget([
    'initialValues' => $initialValues
]) ?>

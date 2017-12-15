<?php

namespace extpoint\yii2\views;

use extpoint\yii2\gii\widgets\AccessRulesEditor\AccessRulesEditor;
use yii\bootstrap\Nav;

/* @var $this \yii\web\View */
/* @var $editorConfig array */

?>

<?= Nav::widget([
    'options' => ['class' => 'nav-tabs'],
    'items' => \Yii::$app->megaMenu->getMenu('admin.access', 1),
]); ?>
<br />

<?= AccessRulesEditor::widget($editorConfig) ?>

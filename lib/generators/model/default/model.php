<?php

namespace app\views;

use extpoint\yii2\gii\generators\model\ModelGenerator;
use extpoint\yii2\gii\models\ModelClass;
use yii\web\View;

/* @var $this View */
/* @var $generator ModelGenerator */
/* @var $modelClass ModelClass */

echo "<?php\n";
?>

namespace <?= $modelClass->namespace ?>;

use <?= $modelClass->metaClass->className ?>;

class <?= $modelClass->name ?> extends <?= $modelClass->metaClass->name . "\n" ?>
{
}

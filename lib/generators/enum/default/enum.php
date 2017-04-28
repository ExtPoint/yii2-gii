<?php

namespace app\views;

use extpoint\yii2\gii\generators\model\ModelGenerator;
use extpoint\yii2\gii\models\EnumClass;
use yii\web\View;

/* @var $this View */
/* @var $generator ModelGenerator */
/* @var $enumClass EnumClass */

echo "<?php\n";
?>

namespace <?= $enumClass->namespace ?>;

use <?= $enumClass->metaClass->className ?>;

class <?= $enumClass->name ?> extends <?= $enumClass->metaClass->name . "\n" ?>
{
}

<?php

namespace app\views;

use extpoint\yii2\gii\generators\crud\CrudGenerator;
use extpoint\yii2\gii\models\ControllerClass;
use yii\web\View;

/* @var $this View */
/* @var $generator CrudGenerator */
/* @var $controllerClass ControllerClass */

echo "<?php\n";
?>

namespace <?= $controllerClass->namespace ?>;

use <?= $controllerClass->metaClass->className ?>;

class <?= $controllerClass->name ?> extends <?= $controllerClass->metaClass->name . "\n" ?>
{
}

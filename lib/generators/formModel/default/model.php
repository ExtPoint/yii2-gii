<?php

namespace app\views;

use extpoint\yii2\gii\generators\model\ModelGenerator;
use extpoint\yii2\gii\models\FormModelClass;
use extpoint\yii2\gii\models\ModelClass;
use yii\web\View;

/* @var $this View */
/* @var $generator ModelGenerator */
/* @var $modelClass ModelClass */
/* @var $formModelClass FormModelClass */

echo "<?php\n";
?>

namespace <?= $formModelClass->namespace ?>;

use yii\db\ActiveQuery;
use <?= $formModelClass->metaClass->className ?>;

class <?= $formModelClass->name ?> extends <?= $formModelClass->metaClass->name . "\n" ?>
{
    public function fields()
    {
        return [
            '*',
        ];
    }

    /**
     * @param ActiveQuery $query
     */
    public function prepare($query)
    {
    }
}

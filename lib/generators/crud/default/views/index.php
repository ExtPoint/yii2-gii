<?php

namespace app\views;

use extpoint\yii2\gii\generators\crud\CrudGenerator;
use extpoint\yii2\gii\models\ControllerClass;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\SearchModelClass;
use yii\web\View;

/* @var $this View */
/* @var $generator CrudGenerator */
/* @var $modelClass ModelClass */
/* @var $controllerClass ControllerClass */
/* @var $searchModelClass SearchModelClass */
/* @var $createActionIndex bool */
/* @var $withSearch bool */
/* @var $withDelete bool */
/* @var $createActionCreate bool */
/* @var $createActionUpdate bool */
/* @var $createActionView bool */

echo "<?php\n";
?>

namespace app\views;

<?php if ($withSearch) { ?>
use <?= $searchModelClass->className ?>;
<?php } ?>
use app\core\widgets\AppGridView;
use yii\data\ActiveDataProvider;
<?php if ($createActionCreate) { ?>
use app\core\widgets\CrudControls;
<?php } ?>
use yii\web\View;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */
<?php if ($withSearch) { ?>
/* @var $searchModel <?= $searchModelClass->name ?> */
<?php } ?>
<?php foreach ($controllerClass->requestFieldsArray as $key) { ?>
/* @var $<?= $key ?> integer */
<?php } ?>

?>

<?php if ($createActionCreate) { ?>
<div class="indent">
<?php if (count($controllerClass->requestFieldsArray) === 0) { ?>
    <?= "<?=" ?> CrudControls::widget() ?>
<?php } else { ?>
    <?= "<?=" ?> CrudControls::widget([
        'actionParams' => [
<?php foreach ($controllerClass->requestFieldsArray as $key) { ?>
            '<?= $key ?>' => $<?= $key ?>,
<?php } ?>
        ],
    ]) ?>
<?php } ?>
</div>
<?php } ?>

<?= "<?=" ?> AppGridView::widget([
    'dataProvider' => $dataProvider,
<?php if ($createActionIndex && $withSearch) { ?>
    'filterModel' => $searchModel,
<?php } ?>
<?php if (count($generator->getGridViewActions()) > 0) { ?>
    'actions' => [
        '<?= implode("',\n        '", $generator->getGridViewActions()) ?>'
    ],
<?php } ?>
<?php if (count($controllerClass->requestFieldsArray) > 0) { ?>
    'actionParams' => [
<?php foreach ($controllerClass->requestFieldsArray as $key) { ?>
        '<?= $key ?>' => $<?= $key ?>,
<?php } ?>
    ],
<?php } ?>
]); ?>

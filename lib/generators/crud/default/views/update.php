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

use yii\web\View;
<?php if ($createActionIndex || $createActionView || $withDelete) { ?>
use app\core\widgets\CrudControls;
<?php } ?>
use app\core\widgets\AppActiveForm;
use <?= $modelClass->className ?>;

/* @var $this View */
/* @var $model <?= $modelClass->name ?> */
<?php foreach ($controllerClass->requestFieldsArray as $key) { ?>
/* @var $<?= $key ?> integer */
<?php } ?>

?>

<?php if ($createActionIndex || $createActionView || $withDelete) { ?>
<div class="indent">
<?php if (count($controllerClass->requestFieldsArray) === 0) { ?>
    <?= "<?=" ?> CrudControls::widget(['model' => $model]) ?>
<?php } else { ?>
    <?= "<?=" ?> CrudControls::widget([
        'model' => $model,
        'actionParams' => [
<?php foreach ($controllerClass->requestFieldsArray as $key) { ?>
            '<?= $key ?>' => $<?= $key ?>,
<?php } ?>
        ],
    ]) ?>
<?php } ?>
</div>
<?php } ?>

<?= "<?php" ?> $form = AppActiveForm::begin() ?>

<?= "<?=" ?> $form->fields($model) ?>
<?= "<?=" ?> $form->controls($model) ?>

<?= "<?php" ?> AppActiveForm::end() ?>

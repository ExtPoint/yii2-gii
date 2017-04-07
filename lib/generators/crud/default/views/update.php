<?php

namespace app\views;

use extpoint\yii2\gii\generators\crud\CrudGenerator;
use yii\web\View;

/* @var $this View */
/* @var $generator CrudGenerator */
/* @var $modelName string */
/* @var $modelClassName string */
/* @var $searchModelName string */
/* @var $searchModelClassName string */
/* @var $createActionIndex bool */
/* @var $withSearch bool */
/* @var $withDelete bool */
/* @var $createActionCreate bool */
/* @var $createActionUpdate bool */
/* @var $createActionView bool */
/* @var $meta array */

echo "<?php\n";
?>

namespace app\views;

use yii\web\View;
<?php if ($createActionIndex || $createActionView || $withDelete) { ?>
use app\core\widgets\MenuLink;
<?php } ?>
use app\core\widgets\AppActiveForm;
use <?= $modelClassName ?>;

/* @var $this View */
/* @var $model <?= $modelName ?> */

?>

<?php if ($createActionIndex || $createActionView || $withDelete) { ?>
<div class="indent">
<?php if ($createActionIndex) { ?>
    <?= "<?=" ?> MenuLink::widget([
        'icon' => 'glyphicon glyphicon-arrow-left',
        'label' => 'К списку',
        'url' => ['index'],
        'options' => [
            'class' => 'btn btn-default',
        ]
    ]) ?>
<?php } ?>
<?php if ($createActionView) { ?>
    <?= "<?=" ?> MenuLink::widget([
        'label' => 'Просмотр',
        'url' => ['view', 'id' => $model->id],
        'visible' => !$model->isNewRecord,
        'options' => [
            'class' => 'btn btn-default',
        ]
    ]) ?>
<?php } ?>
<?php if ($withDelete) { ?>

    <div class="pull-right">
        <?= "<?=" ?> MenuLink::widget([
            'icon' => 'glyphicon glyphicon-remove',
            'label' => 'Удалить',
            'url' => ['delete', 'id' => $model->id],
            'visible' => !$model->isNewRecord,
            'options' => [
                'class' => 'btn btn-danger',
                'data-confirm' => 'Удалить запись?',
                'data-method' => 'post',
            ]
        ]) ?>
    </div>
<?php } ?>
</div>
<?php } ?>

<?= "<?php" ?> $form = AppActiveForm::begin() ?>

<?= "<?=" ?> $form->fields($model) ?>
<?= "<?=" ?> $form->controls($model) ?>

<?= "<?php" ?> AppActiveForm::end() ?>

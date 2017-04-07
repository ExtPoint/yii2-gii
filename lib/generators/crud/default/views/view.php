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
<?php if ($createActionIndex || $createActionUpdate || $withDelete) { ?>
    use app\core\widgets\MenuLink;
<?php } ?>
use app\core\widgets\AppDetailView;
use <?= $modelClassName ?>;

/* @var $this View */
/* @var $model <?= $modelName ?> */

?>

<?php if ($createActionIndex || $createActionUpdate || $withDelete) { ?>
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
<?php if ($createActionUpdate) { ?>
    <?= "<?=" ?> MenuLink::widget([
        'label' => 'Просмотр',
        'url' => ['update', 'id' => $model->id],
        'options' => [
            'class' => 'btn btn-warning',
        ]
    ]) ?>
<?php } ?>
<?php if ($withDelete) { ?>

    <div class="pull-right">
        <?= "<?=" ?> MenuLink::widget([
            'icon' => 'glyphicon glyphicon-remove',
            'label' => 'Удалить',
            'url' => ['delete', 'id' => $model->id],
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

<?= "<?=" ?> AppDetailView::widget([
    'model' => $model,
]) ?>

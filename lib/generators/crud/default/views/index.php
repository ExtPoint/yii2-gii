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

<?php if ($withSearch) { ?>
use <?= $searchModelClassName ?>;
<?php } ?>
use app\core\widgets\AppGridView;
use yii\data\ActiveDataProvider;
<?php if ($createActionCreate) { ?>
use app\core\widgets\MenuLink;
<?php } ?>
use yii\web\View;

/* @var $this View */
/* @var $dataProvider ActiveDataProvider */
<?php if ($withSearch) { ?>
/* @var $searchModel <?= $searchModelName ?> */
<?php } ?>

?>

<?php if ($createActionCreate) { ?>
    <div class="indent">
        <?= "<?=" ?> MenuLink::widget([
            'icon' => 'glyphicon glyphicon-plus',
            'label' => 'Добавить',
            'url' => ['create'],
            'options' => [
                'class' => 'btn btn-success',
            ]
        ]) ?>
    </div>
<?php } ?>

<?= "<?=" ?> AppGridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
<?php if (count($generator->getGridViewActions()) > 0) { ?>
    'actions' => ['<?= implode("', '", $generator->getGridViewActions()) ?>'],
<?php } ?>
]); ?>

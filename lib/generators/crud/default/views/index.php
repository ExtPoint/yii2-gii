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
/* @var $requestFields string[] */

echo "<?php\n";
?>

namespace app\views;

<?php if ($withSearch) { ?>
use <?= $searchModelClassName ?>;
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
/* @var $searchModel <?= $searchModelName ?> */
<?php } ?>
<?php foreach ($requestFields as $requestField) { ?>
/* @var $<?= $requestField ?> integer */
<?php } ?>

?>

<?php if ($createActionCreate) { ?>
<div class="indent">
<?php if (count($requestFields) === 0) { ?>
    <?= "<?=" ?> CrudControls::widget() ?>
<?php } else { ?>
    <?= "<?=" ?> CrudControls::widget([
        'actionParams' => [
<?php foreach ($requestFields as $requestField) { ?>
            '<?= $requestField ?>' => $<?= $requestField ?>,
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
<?php if (count($requestFields) > 0) { ?>
    'actionParams' => [
    <?php foreach ($requestFields as $requestField) { ?>
        '<?= $requestField ?>' => $<?= $requestField ?>,
    <?php } ?>
    ],
<?php } ?>
]); ?>

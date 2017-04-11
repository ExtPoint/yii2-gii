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

use yii\web\View;
<?php if ($createActionIndex || $createActionUpdate || $withDelete) { ?>
use app\core\widgets\CrudControls;
<?php } ?>
use app\core\widgets\AppDetailView;
use <?= $modelClassName ?>;

/* @var $this View */
/* @var $model <?= $modelName ?> */
<?php foreach ($requestFields as $requestField) { ?>
/* @var $<?= $requestField ?> integer */
<?php } ?>

?>

<?php if ($createActionIndex || $createActionUpdate || $withDelete) { ?>
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

<?= "<?=" ?> AppDetailView::widget([
    'model' => $model,
]) ?>

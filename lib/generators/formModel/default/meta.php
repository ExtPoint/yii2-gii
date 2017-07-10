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

$useClasses = [];
$rules = $formModelClass->metaClass->renderRules($useClasses);
$behaviors = $formModelClass->metaClass->renderBehaviors('            ', $useClasses);
$meta = $formModelClass->metaClass->renderMeta('        ', $useClasses);

echo "<?php\n";
?>

namespace <?= $formModelClass->metaClass->namespace ?>;

use extpoint\yii2\base\FormModel;
use extpoint\yii2\traits\ISearchModelTrait;
use extpoint\yii2\traits\SearchModelTrait;
<?php foreach (array_unique($useClasses) as $relationClassName) { ?>
use <?= $relationClassName ?>;
<?php } ?>
<?php if ($modelClass) { ?>
use <?= $modelClass->className ?>;
<?php } ?>

abstract class <?= $formModelClass->metaClass->name ?> extends FormModel implements ISearchModelTrait
{
    use SearchModelTrait;

<?php foreach ($formModelClass->metaClass->metaWithChild as $metaItem) { ?>
    public $<?= $metaItem->name ?>;
<?php } ?>

<?php if (count($formModelClass->metaClass->properties) > 0) { ?>
<?php foreach ($formModelClass->metaClass->properties as $key => $value) { ?>
    public $<?= $key ?><?= $value !== null ? ' = ' . $value : '' ?>;
<?php } ?>

<?php } ?>
<?php if (!empty($rules)) { ?>
    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . ",\n        " ?>];
    }
<?php } ?>
<?php if (!empty($behaviors)) { ?>

    public function behaviors()
    {
        return [
            <?= $behaviors ?>
        ];
    }
<?php } ?>
<?php if ($modelClass) { ?>

    public function createQuery()
    {
        return <?= $modelClass->name ?>::find();
    }
<?php } ?>

    public static function meta()
    {
        return <?= $meta ?>;
    }
}

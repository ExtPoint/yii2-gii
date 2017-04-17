<?php

namespace app\views;

use extpoint\yii2\gii\generators\model\ModelGenerator;
use extpoint\yii2\gii\models\ModelClass;
use yii\web\View;

/* @var $this View */
/* @var $generator ModelGenerator */
/* @var $modelClass ModelClass */

$useClasses = [];
$rules = $generator->exportRules($useClasses);

if (count($modelClass->metaClass->relations) > 0) {
    $useClasses[] = 'yii\db\ActiveQuery';
}
foreach ($modelClass->metaClass->relations as $relation) {
    $useClasses[] = $relation->relationClass->className;
}

$properties = [];
foreach ($modelClass->metaClass->meta as $metaItem) {
    if ($metaItem->appType === 'relation' && !$metaItem->getDbType()) {
        $relation = $metaItem->metaClass->getRelation($metaItem->relationName);
        $properties[$metaItem->name] = $relation && !$relation->isHasOne ? ' = []' : '';
    }
}

echo "<?php\n";
?>

namespace <?= $modelClass->metaClass->namespace ?>;

use app\core\base\AppModel;
<?php foreach (array_unique($useClasses) as $relationClassName) { ?>
use <?= $relationClassName ?>;
<?php } ?>

/**
<?php foreach ($modelClass->metaClass->meta as $metaItem) {
    if ($metaItem->getDbType()) { ?>
 * @property <?= "{$metaItem->phpDocType} \${$metaItem->name}\n" ?>
<?php }
} ?>
<?php foreach ($modelClass->metaClass->relations as $relation) { ?>
 * @property-read <?= $relation->relationClass->name ?><?= $relation->isHasOne ? '[]' : '' ?> <?= "\${$relation->name}\n" ?>
<?php } ?>
 */
abstract class <?= $modelClass->metaClass->name ?> extends AppModel
{
<?php if (count($properties) > 0) { ?>
<?php foreach ($properties as $key => $value) { ?>
    public $<?= $key ?><?= $value ?>;
<?php } ?>

<?php } ?>
    public static function tableName()
    {
        return '<?= $modelClass->tableName ?>';
    }
<?php if (!empty($rules)) { ?>

    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . ",\n        " ?>];
    }
<?php } ?>
<?php foreach ($modelClass->metaClass->relations as $relation) { ?>

    /**
     * @return ActiveQuery
     */
    public function get<?= ucfirst($relation->name) ?>()
    {
<?php if ($relation->isHasOne || $relation->isHasMany) { ?>
        return $this-><?= $relation->type ?>(<?= $relation->relationClass->name ?>::className(), ['<?= $relation->relationKey ?>' => '<?= $relation->selfKey ?>']);
<?php } elseif ($relation->isManyMany) { ?>
        return $this->hasMany(<?= $relation->relationClass->name ?>::className(), ['<?= $relation->relationKey ?>' => '<?= $relation->viaRelationKey ?>'])
            ->viaTable('<?= $relation->viaTable ?>', ['<?= $relation->viaSelfKey ?>' => '<?= $relation->selfKey ?>']);
<?php } ?>
    }
<?php } ?>

    public static function meta()
    {
        return <?= $modelClass->metaClass->renderMeta('        ') ?>;
    }
}

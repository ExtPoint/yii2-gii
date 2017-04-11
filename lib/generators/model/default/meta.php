<?php

namespace app\views;

use extpoint\yii2\gii\generators\model\ModelGenerator;
use yii\helpers\ArrayHelper;
use yii\web\View;

/* @var $this View */
/* @var $generator ModelGenerator */
/* @var $tableName string */
/* @var $namespace string */
/* @var $className string */
/* @var $meta array */
/* @var $relations array */

$useClasses = [];
$rules = $generator->exportRules($useClasses);

if (count($relations) > 0) {
    $useClasses[] = 'yii\db\ActiveQuery';
}
$useClasses = array_merge($useClasses, ArrayHelper::getColumn($relations, 'relationModelClassName'));

echo "<?php\n";
?>

namespace <?= $namespace ?>;

use app\core\base\AppModel;
<?php foreach (array_unique($useClasses) as $relationClassName) { ?>
use <?= $relationClassName ?>;
<?php } ?>

/**
<?php foreach ($meta as $metaItem) { ?>
 * @property <?= "{$generator->getPhpDocType($metaItem['dbType'])} \${$metaItem['name']}\n" ?>
<?php } ?>
<?php foreach ($relations as $relation) { ?>
 * @property-read <?= $relation['model']['name'] ?><?= $relation['type'] !== 'hasOne' ? '[]' : '' ?> <?= "\${$relation['name']}\n" ?>
<?php } ?>
 */
abstract class <?= $className ?> extends AppModel
{
    public static function tableName()
    {
        return '<?= $tableName ?>';
    }
<?php if (!empty($rules)) { ?>

    public function rules()
    {
        return [<?= "\n            " . implode(",\n            ", $rules) . ",\n        " ?>];
    }
<?php } ?>
<?php foreach ($relations as $relation) { ?>

    /**
     * @return ActiveQuery
     */
    public function get<?= ucfirst($relation['name']) ?>()
    {
<?php if ($relation['type'] !== 'manyMany') { ?>
        return $this-><?= $relation['type'] === 'hasOne' ? 'hasOne' : 'hasMany' ?>(<?= $relation['model']['name'] ?>::className(), ['<?= $relation['relationKey'] ?>' => '<?= $relation['selfKey'] ?>']);
<?php } else { ?>
        return $this->hasMany(<?= $relation['model']['name'] ?>::className(), ['<?= $relation['relationKey'] ?>' => '<?= $relation['viaRelationKey'] ?>'])
            ->viaTable('<?= $relation['viaTable'] ?>', ['<?= $relation['viaSelfKey'] ?>' => '<?= $relation['selfKey'] ?>']);
<?php } ?>
    }
<?php } ?>

    public static function meta()
    {
        return <?= $generator->exportMeta('        ') ?>;
    }
}

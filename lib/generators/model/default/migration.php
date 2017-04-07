<?php

namespace app\views;

use extpoint\yii2\gii\generators\model\ModelGenerator;
use yii\db\ColumnSchema;
use yii\web\View;

/* @var $this View */
/* @var $generator ModelGenerator */
/* @var $tableName string */
/* @var $className string */
/* @var $meta array */
/* @var $addColumn array */
/* @var $alterColumn array */
/* @var $alterColumnDown array */
/* @var $dropColumn array */
/* @var $tablesToCreate array */
/* @var $foreignKeys array */

echo "<?php\n";
?>

use extpoint\yii2\base\Migration;

class <?= $className ?> extends Migration
{
    public function up() {
<?php foreach ($addColumn as $item) { ?>
        $this->addColumn('<?= $tableName ?>', '<?= $item['name'] ?>', '<?= $generator->getColumnType($item) ?>');
<?php } ?>
<?php foreach ($alterColumn as $item) { ?>
        $this->alterColumn('<?= $tableName ?>', '<?= $item['name'] ?>', '<?= $generator->getColumnType($item) ?>');
<?php } ?>
<?php foreach ($dropColumn as $item) { ?>
        $this->dropColumn('<?= $tableName ?>', '<?= $item['name'] ?>');
<?php } ?>
<?php foreach ($tablesToCreate as $item) { ?>
        $this->createTable('<?= $item['table'] ?>', [
<?php foreach ($item['columns'] as $key => $type) { ?>
            '<?= $key ?>' => '<?= $type ?>',
<?php } ?>
        ]);
<?php } ?>
<?php foreach ($foreignKeys as $item) { ?>
        $this->createForeignKey('<?= $item['table'] ?>', '<?= $item['key'] ?>', '<?= $item['refTable'] ?>', '<?= $item['refKey'] ?>');
<?php } ?>
    }

    public function down() {
<?php foreach ($foreignKeys as $item) { ?>
        $this->deleteForeignKey('<?= $item['table'] ?>', '<?= $item['key'] ?>', '<?= $item['refTable'] ?>', '<?= $item['refKey'] ?>');
<?php } ?>
<?php foreach ($tablesToCreate as $item) { ?>
        $this->dropTable('<?= $item['table'] ?>');
<?php } ?>
<?php foreach ($dropColumn as $item) { ?>
        $this->addColumn('<?= $tableName ?>', '<?= $item['name'] ?>', '<?= $generator->getColumnType($item) ?>');
<?php } ?>
<?php foreach ($alterColumnDown as $item) { ?>
        $this->alterColumn('<?= $tableName ?>', '<?= $item['name'] ?>', '<?= $generator->getColumnType($item) ?>');
<?php } ?>
<?php foreach ($addColumn as $item) { ?>
        $this->dropColumn('<?= $tableName ?>', '<?= $item['name'] ?>');
<?php } ?>
    }
}

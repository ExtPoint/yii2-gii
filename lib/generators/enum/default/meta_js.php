<?php

namespace app\views;

use extpoint\yii2\gii\generators\model\ModelGenerator;
use extpoint\yii2\gii\models\EnumClass;
use yii\web\View;

/* @var $this View */
/* @var $generator ModelGenerator */
/* @var $enumClass EnumClass */

$labels = $enumClass->metaClass->renderJsLabels('        ');
$cssClasses = $enumClass->metaClass->renderJsCssClasses('        ');

?>
import Enum from 'extpoint-yii2/base/Enum';

export default class <?= $enumClass->metaClass->name ?> extends Enum {

<?php foreach ($enumClass->metaClass->meta as $enumMetaItem) { ?>
    static <?= $enumMetaItem->constName ?> = <?= is_numeric($enumMetaItem->value) ? $enumMetaItem->value :  "'" . $enumMetaItem->value . "'" ?>;
<?php } ?>

    static getLabels() {
        return <?= $labels ?>;
    }
<?php if (!empty($cssClasses)) { ?>

    static getCssClasses() {
        return <?= $cssClasses ?>;
    }
<?php } ?>
}

<?php

namespace app\views;

use extpoint\yii2\gii\generators\model\ModelGenerator;
use extpoint\yii2\gii\models\ModelClass;
use yii\web\View;

/* @var $this View */
/* @var $generator ModelGenerator */
/* @var $modelClass ModelClass */

$import = [];
$meta = $modelClass->metaClass->renderJsMeta('        ', $import);

?>
import Model from 'extpoint-yii2/base/Model';
<?= !empty($import) ? "\n" . implode("\n", array_unique($import)) . "\n" : '' ?>

export default class <?= $modelClass->metaClass->name ?> extends Model {

    static className = '<?= str_replace('\\', '\\\\', $modelClass->className) ?>';

    static meta() {
        return <?= $meta ?>;
    }

}

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
import ModelMeta from 'extpoint-yii2/base/ModelMeta';
<?= !empty($import) ? implode("\n", $import) . "\n" : '' ?>

export default class <?= $modelClass->metaClass->name ?> extends ModelMeta {

    static className = '<?= str_replace('\\', '\\\\', $modelClass->className) ?>';

    static meta() {
        return <?= $meta ?>;
    }

}

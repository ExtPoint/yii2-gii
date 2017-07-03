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

$import = [];
$meta = $formModelClass->metaClass->renderJsMeta('        ', $import);

?>
import Model from 'extpoint-yii2/base/Model';
<?= !empty($import) ? implode("\n", $import) . "\n" : '' ?>

export default class <?= $formModelClass->metaClass->name ?> extends Model {

    static className = '<?= str_replace('\\', '\\\\', $formModelClass->className) ?>';

    static meta() {
        return <?= $meta ?>;
    }

}

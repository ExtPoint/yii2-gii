<?php

namespace app\views;

use extpoint\yii2\gii\generators\crud\CrudGenerator;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\SearchModelClass;
use yii\web\View;

/* @var $this View */
/* @var $generator CrudGenerator */
/* @var $modelClass ModelClass */
/* @var $searchModelClass SearchModelClass */

echo "<?php\n";
?>

namespace <?= $searchModelClass->namespace ?>;

use <?= $modelClass->className ?>;
use app\core\traits\SearchModelTrait;
use yii\db\ActiveQuery;

class <?= $searchModelClass->name ?> extends <?= $modelClass->name ?>

{
    use SearchModelTrait;

    public function rules()
    {
        return [
            <?= $searchModelClass->renderSearchRules('            ') ?>,
        ];
    }

    /**
     * @return array
     */
    public function createProvider()
    {
        return [
            'sort' => [
                'attributes' => ['id'],
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ];
    }

    /**
     * @param ActiveQuery $query
     */
    public function prepare($query)
    {
        <?= $searchModelClass->renderSearchConditions('        ') ?>
    }

}

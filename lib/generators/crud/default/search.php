<?php

namespace app\views;

use extpoint\yii2\gii\generators\crud\CrudGenerator;
use yii\web\View;

/* @var $this View */
/* @var $generator CrudGenerator */
/* @var $namespace string */
/* @var $className string */
/* @var $parentModelName string */
/* @var $parentModelClassName string */

echo "<?php\n";
?>

namespace <?= $namespace ?>;

use <?= $parentModelClassName ?>;
use app\core\traits\SearchModelTrait;
use yii\db\ActiveQuery;

class <?= $className ?> extends <?= $parentModelName ?>

{
    use SearchModelTrait;

    public function rules()
    {
        return [
            <?= implode(",\n            ", $generator->getSearchRules()) ?>,
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function createQuery()
    {
        return <?= $parentModelName ?>::find();
    }

    /**
     * @return array
     */
    public function createProvider()
    {
        return [
            'sort' => [
                'attributes' => 'id',
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
        <?= implode("\n        ", $generator->getSearchConditions()) ?>
    }

}

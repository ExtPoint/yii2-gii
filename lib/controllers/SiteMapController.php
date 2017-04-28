<?php

namespace extpoint\yii2\gii\controllers;

use extpoint\yii2\gii\generators\enum\EnumGenerator;
use extpoint\yii2\gii\generators\model\ModelGenerator;
use extpoint\yii2\gii\generators\crud\CrudGenerator;
use extpoint\yii2\gii\generators\module\ModuleGenerator;
use extpoint\yii2\base\Controller;
use extpoint\yii2\gii\models\EnumClass;
use extpoint\yii2\gii\models\EnumMetaItem;
use extpoint\yii2\gii\models\MetaItem;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\ModuleClass;
use extpoint\yii2\gii\models\Relation;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

class SiteMapController extends Controller
{
    public static function coreMenuItems() {
        return [
            'site-map' => [
                'label' => 'Карта сайта',
                'url' => ['/gii/site-map/index'],
                'urlRule' => 'admin/site-map',
                'order' => 499,
                'roles' => 'admin',
                'visible' => YII_ENV_DEV,
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'items' => \Yii::$app->megaMenu->getItems(),
        ]);
    }

}

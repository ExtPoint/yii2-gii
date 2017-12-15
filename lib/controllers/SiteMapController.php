<?php

namespace extpoint\yii2\gii\controllers;

use extpoint\yii2\base\Controller;
use extpoint\yii2\gii\GiiModule;

class SiteMapController extends Controller
{
    public static function coreMenuItems()
    {
        return [
            'site-map' => [
                'label' => 'Карта сайта',
                'url' => ['/gii/site-map/index'],
                'urlRule' => 'admin/site-map',
                'order' => 499,
                'accessCheck' => [GiiModule::className(), 'accessCheck'],
                'visible' => YII_ENV_DEV,
            ],
        ];
    }

    public function actionIndex()
    {
        $testItem = null;
        $testUrl = \Yii::$app->request->get('url');
        if ($testUrl) {
            $testRequest = clone \Yii::$app->request;
            $testRequest->pathInfo = ltrim($testUrl, '/');
            $parseInfo = \Yii::$app->urlManager->parseRequest($testRequest);
            if ($parseInfo) {
                $testRoute = [$parseInfo[0] ? '/' . $parseInfo[0] : ''] + $parseInfo[1];
                $testItem = \Yii::$app->megaMenu->getItem($testRoute);
            }
        }

        return $this->render('index', [
            'items' => \Yii::$app->megaMenu->getItems(),
            'testItem' => $testItem,
            'testUrl' => $testUrl,
        ]);
    }

}

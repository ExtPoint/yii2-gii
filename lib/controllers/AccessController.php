<?php

namespace extpoint\yii2\gii\controllers;

use extpoint\megamenu\MegaMenuItem;
use extpoint\yii2\base\Controller;
use extpoint\yii2\components\AuthManager;
use extpoint\yii2\gii\GiiModule;
use extpoint\yii2\gii\models\AuthPermissionSync;
use extpoint\yii2\gii\models\ModelClass;
use yii\helpers\ArrayHelper;

class AccessController extends Controller
{
    const MODEL_PREFIX = 'm';
    const ACTION_PREFIX = 'u';
    const SEPARATOR = '::';

    public static function coreMenuItems()
    {
        return [
            'access' => [
                'label' => 'Права доступа',
                'order' => 499,
                'accessCheck' => [GiiModule::className(), 'accessCheck'],
                'visible' => YII_ENV_DEV,
                'redirectToChild' => true,
                'items' => [
                    'models' => [
                        'label' => 'Модели',
                        'url' => ['/gii/access/models'],
                        'urlRule' => 'admin/access/models',
                    ],
                    'actions' => [
                        'label' => 'Страницы',
                        'url' => ['/gii/access/actions'],
                        'urlRule' => 'admin/access/actions',
                    ],
                ]
            ],
        ];
    }

    public function actionModels()
    {
        AuthPermissionSync::syncModels();
        return $this->_actionEditor(AuthManager::RULE_PREFIX_MODEL);
    }

    public function actionActions()
    {
        AuthPermissionSync::syncActions();
        return $this->_actionEditor(AuthManager::RULE_PREFIX_ACTION);
    }

    public function _actionEditor($prefix)
    {
        AuthPermissionSync::syncRoles();

        if (\Yii::$app->request->isPost) {
            $this->_save($prefix, \Yii::$app->request->post());
            return $this->refresh();
        }

        return $this->render('editor', [
            'editorConfig' => [
                'prefix' => $prefix,
                'enableInlineMode' => $prefix === AuthManager::RULE_PREFIX_MODEL,
            ],
        ]);
    }

    protected function _save($prefix, $data)
    {
        $allNames = ArrayHelper::getColumn(AuthPermissionSync::getPermissions($prefix), 'name');

        $auth = \Yii::$app->authManager;
        foreach ($auth->getRoles() as $role) {
            $rules = ArrayHelper::getValue($data, ['rules', $role->name], []);
            $addedNames = [];
            $prevNames = ArrayHelper::getColumn($auth->getPermissionsByRole($role->name), 'name');
            $prevNames = array_filter($prevNames, function($name) use ($allNames) {
                return in_array($name, $allNames);
            });

            foreach ($rules as $rule => $bool) {
                if (!$bool) {
                    continue;
                }

                // Find parent permission and check checked
                $isParentChecked = false;
                foreach ($allNames as $permissionName) {
                    $childNames = ArrayHelper::getColumn($auth->getChildren($permissionName), 'name');
                    if (in_array($rule, $childNames) && ArrayHelper::keyExists($permissionName, $rules)) {
                        $isParentChecked = true;
                        break;
                    }
                }

                if (!$isParentChecked) {
                    $addedNames[] = $rule;
                    $permission = $auth->getPermission($rule);

                    AuthPermissionSync::safeAddChild($role, $permission);
                }
            }

            // Remove unchecked
            foreach (array_diff($prevNames, $addedNames) as $name) {
                $auth->removeChild($role, $auth->getPermission($name));
            }
        }
    }
}

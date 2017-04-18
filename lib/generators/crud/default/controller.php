<?php

namespace app\views;

use extpoint\yii2\gii\generators\crud\CrudGenerator;
use extpoint\yii2\gii\models\ControllerClass;
use extpoint\yii2\gii\models\ModelClass;
use extpoint\yii2\gii\models\SearchModelClass;
use yii\web\View;

/* @var $this View */
/* @var $generator CrudGenerator */
/* @var $modelClass ModelClass */
/* @var $controllerClass ControllerClass */
/* @var $searchModelClass SearchModelClass */
/* @var $createActionIndex bool */
/* @var $withSearch bool */
/* @var $withDelete bool */
/* @var $createActionCreate bool */
/* @var $createActionUpdate bool */
/* @var $createActionView bool */

echo "<?php\n";
?>

namespace <?= $controllerClass->namespace ?>;

use Yii;
use app\core\base\AppController;
use <?= $modelClass->className ?>;
<?php if ($createActionIndex && $withSearch) { ?>
use <?= $searchModelClass->className ?>;
<?php } ?>
<?php if ($createActionIndex && !$withSearch) { ?>
use yii\data\ActiveDataProvider;
<?php } ?>
<?php if (($createActionIndex && $withDelete) || $createActionView) { ?>
use yii\web\ForbiddenHttpException;
<?php } ?>

class <?= $controllerClass->name ?> extends AppController
{
    public static function coreMenuItems()
    {
        return [
            [
                'label' => '<?= $controllerClass->title ?>',
<?php if ($createActionIndex) { ?>
                'url' => ['<?= $controllerClass->routePrefix ?>/index'],
                'urlRule' => '<?= $controllerClass->url ?>',
<?php } ?>
<?php if (count($controllerClass->rolesArray) > 0) { ?>
                'roles' => <?= $controllerClass->renderRoles() ?>,
<?php } ?>
<?php if ($createActionCreate || $createActionUpdate || $createActionUpdate || $createActionView) { ?>
                'items' => [
<?php if ($createActionCreate) { ?>
                    [
                        'label' => 'Добавление',
                        'url' => ['<?= $controllerClass->routePrefix ?>/create'],
                        'urlRule' => '<?= $controllerClass->url ?>/create',
                    ],
<?php } ?>
<?php if ($createActionUpdate) { ?>
                    [
                        'label' => 'Редактирование',
                        'url' => ['<?= $controllerClass->routePrefix ?>/update'],
                        'urlRule' => '<?= $controllerClass->url ?>/update/<<?= $modelClass->requestParamName ?>:\d+>',
                    ],
<?php } ?>
<?php if ($createActionView) { ?>
                    [
                        'label' => 'Просмотр',
                        'url' => ['<?= $controllerClass->routePrefix ?>/view'],
                        'urlRule' => '<?= $controllerClass->url ?>/<<?= $modelClass->requestParamName ?>:\d+>',
                        'modelClass' => <?= $modelClass->name ?>::className(),
                    ],
<?php } ?>
<?php if ($withDelete) { ?>
                    [
                        'url' => ['<?= $controllerClass->routePrefix ?>/delete'],
                        'urlRule' => '<?= $controllerClass->url ?>/delete/<<?= $modelClass->requestParamName ?>:\d+>',
                    ],
<?php } ?>
                ],
<?php } ?>
            ],
        ];
    }
<?php if ($createActionIndex && $withSearch) { ?>

    public function actionIndex(<?= $controllerClass->renderActionArguments() ?>)
    {
        $searchModel = new <?= $searchModelClass->name ?>();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
<?php foreach ($controllerClass->requestFieldsArray as $key) { ?>
            '<?= $key ?>' => $<?= $key ?>,
<?php } ?>
        ]);
    }
<?php } ?>
<?php if ($createActionIndex && !$withSearch) { ?>

    public function actionIndex(<?= $controllerClass->renderActionArguments() ?>)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelClass->name ?>::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
<?php foreach ($controllerClass->requestFieldsArray as $key) { ?>
            '<?= $key ?>' => $<?= $key ?>,
<?php } ?>
        ]);
    }
<?php } ?>
<?php if ($createActionView) { ?>

    public function actionView(<?= $controllerClass->renderActionArguments([$modelClass->requestParamName]) ?>)
    {
        $model = <?= $modelClass->name ?>::findOrPanic($<?= $modelClass->requestParamName ?>);
        if (!$model->canView(Yii::$app->user->model)) {
            throw new ForbiddenHttpException();
        }
        return $this->render('view', [
            'model' => $model,
        ]);
    }
<?php } ?>
<?php if ($createActionCreate) { ?>

    public function actionCreate(<?= $controllerClass->renderActionArguments() ?>)
    {
        $model = new <?= $modelClass->name ?>();
<?php foreach ($controllerClass->requestFieldsArray as $key) { ?>
        $model-><?= $key ?> = $<?= $key ?>;
<?php } ?>

        if ($model->load(Yii::$app->request->post()) && $model->canCreate(Yii::$app->user->model) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Запись добавлена');
<?php if ($createActionView) { ?>
            return $this->redirect(<?= $controllerClass->renderRoute('view', [$modelClass->requestParamName => '$model->primaryKey']) ?>);
<?php } elseif ($createActionIndex) { ?>
            return $this->redirect(<?= $controllerClass->renderRoute('index') ?>);
<?php } else { ?>
            return $this->refresh();
<?php } ?>
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }
<?php } ?>
<?php if ($createActionUpdate) { ?>

    public function actionUpdate(<?= $controllerClass->renderActionArguments([$modelClass->requestParamName]) ?>)
    {
        $model = <?= $modelClass->name ?>::findOrPanic($<?= $modelClass->requestParamName ?>);
        $model->fillManyMany();

        if ($model->load(Yii::$app->request->post()) && $model->canUpdate(Yii::$app->user->model) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Запись обновлена');
<?php if ($createActionView) { ?>
            return $this->redirect(<?= $controllerClass->renderRoute('view', [$modelClass->requestParamName => '$model->primaryKey']) ?>);
<?php } elseif ($createActionIndex) { ?>
            return $this->redirect(<?= $controllerClass->renderRoute('index') ?>);
<?php } else { ?>
            return $this->refresh();
<?php } ?>
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }
<?php } ?>
<?php if ($createActionIndex && $withDelete) { ?>

    public function actionDelete(<?= $controllerClass->renderActionArguments([$modelClass->requestParamName]) ?>)
    {
        $model = <?= $modelClass->name ?>::findOrPanic($<?= $modelClass->requestParamName ?>);
        if ($model->canDelete(Yii::$app->user->model)) {
            $model->deleteOrPanic();
        } else {
            throw new ForbiddenHttpException();
        }
        return $this->redirect(<?= $controllerClass->renderRoute('index') ?>);
    }
<?php } ?>

}

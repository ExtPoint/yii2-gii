<?php

namespace app\views;

use extpoint\yii2\gii\generators\crud\CrudGenerator;
use yii\web\View;

/* @var $this View */
/* @var $generator CrudGenerator */
/* @var $namespace string */
/* @var $className string */
/* @var $routePrefix string */
/* @var $pkParam string */
/* @var $modelName string */
/* @var $modelClassName string */
/* @var $searchModelClassName string */
/* @var $searchModelName string */
/* @var $createActionIndex bool */
/* @var $withSearch bool */
/* @var $withDelete bool */
/* @var $createActionCreate bool */
/* @var $createActionUpdate bool */
/* @var $createActionView bool */
/* @var $title string */
/* @var $url string */
/* @var $roles string[] */
/* @var $requestFields string[] */

$requestParamsArray = [];
foreach ($requestFields as $requestField) {
    $requestParamsArray[] = "'$requestField' => \$requestField";
}
$redirectParamsString = count($requestParamsArray) > 0 ? ', ' . implode(', ', $requestParamsArray) : '';

echo "<?php\n";
?>

namespace <?= $namespace ?>;

use Yii;
use app\core\base\AppController;
use <?= $modelClassName ?>;
<?php if ($createActionCreate && $withSearch) { ?>
use <?= $searchModelClassName ?>;
<?php } ?>
<?php if ($createActionCreate && !$withSearch) { ?>
use yii\data\ActiveDataProvider;
<?php } ?>
<?php if ($createActionIndex && $withDelete) { ?>
use yii\web\ForbiddenHttpException;
<?php } ?>

class <?= $className ?> extends AppController
{
    public static function coreMenuItems()
    {
        return [
            [
                'label' => '<?= $title ?>',
<?php if ($createActionIndex) { ?>
                'url' => ['<?= $routePrefix ?>/index'],
                'urlRule' => '<?= $url ?>',
<?php } ?>
<?php if (count($roles) === 1) { ?>
                'roles' => '<?= $roles[0] ?>',
<?php } ?>
<?php if (count($roles) > 1) { ?>
                'roles' => ['<?= implode('\', \'', $roles) ?>'],
<?php } ?>
<?php if ($createActionCreate || $createActionUpdate || $createActionUpdate || $createActionView) { ?>
                'items' => [
<?php if ($createActionCreate) { ?>
                    [
                        'label' => 'Добавление',
                        'url' => ['<?= $routePrefix ?>/create'],
                        'urlRule' => '<?= $url ?>/create',
                    ],
<?php } ?>
<?php if ($createActionUpdate) { ?>
                    [
                        'label' => 'Редактирование',
                        'url' => ['<?= $routePrefix ?>/update'],
                        'urlRule' => '<?= $url ?>/update/<<?= $pkParam ?>:\d+>',
                    ],
<?php } ?>
<?php if ($createActionView) { ?>
                    [
                        'label' => 'Просмотр',
                        'url' => ['<?= $routePrefix ?>/view'],
                        'urlRule' => '<?= $url ?>/<<?= $pkParam ?>:\d+>',
                        'modelClass' => <?= $modelName ?>::className(),
                    ],
<?php } ?>
<?php if ($withDelete) { ?>
                    [
                        'url' => ['<?= $routePrefix ?>/delete'],
                        'urlRule' => '<?= $url ?>/delete/<<?= $pkParam ?>:\d+>',
                    ],
<?php } ?>
                ],
<?php } ?>
            ],
        ];
    }
<?php if ($createActionIndex && $withSearch) { ?>

    public function actionIndex(<?= count($requestFields) > 0 ? '$' . implode(', $', $requestFields) : '' ?>)
    {
        $searchModel = new <?= $searchModelName ?>();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
<?php foreach ($requestFields as $requestField) { ?>
            '<?= $requestField ?>' => $<?= $requestField ?>,
<?php } ?>
        ]);
    }
<?php } ?>
<?php if ($createActionIndex && !$withSearch) { ?>

    public function actionIndex(<?= count($requestFields) > 0 ? '$' . implode(', $', $requestFields) : '' ?>)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelName ?>::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
<?php foreach ($requestFields as $requestField) { ?>
            '<?= $requestField ?>' => $<?= $requestField ?>,
<?php } ?>
        ]);
    }
<?php } ?>
<?php if ($createActionView) { ?>

    public function actionView(<?= count($requestFields) > 0 ? '$' . implode(', $', $requestFields) : '' ?>, $id)
    {
        $model = <?= $modelName ?>::findOrPanic($id);
        if (!$model->canView(Yii::$app->user->model)) {
            throw new ForbiddenHttpException();
        }
        return $this->render('view', [
            'model' => $model,
        ]);
    }
<?php } ?>
<?php if ($createActionCreate) { ?>

    public function actionCreate(<?= count($requestFields) > 0 ? '$' . implode(', $', $requestFields) : '' ?>)
    {
        $model = new <?= $modelName ?>();
<?php foreach ($requestFields as $requestField) { ?>
        $model-><?= $requestField ?> = $<?= $requestField ?>;
<?php } ?>

        if ($model->load(Yii::$app->request->post()) && $model->canCreate(Yii::$app->user->model) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Запись добавлена');
<?php if ($createActionView) { ?>
            return $this->redirect(['view'<?= $redirectParamsString ?>, '<?= $pkParam ?>' => $model->primaryKey]);
<?php } elseif ($createActionIndex) { ?>
            return $this->redirect(['index'<?= $redirectParamsString ?>]);
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

    public function actionUpdate(<?= count($requestFields) > 0 ? '$' . implode(', $', $requestFields) : '' ?>, $id)
    {
        $model = <?= $modelName ?>::findOrPanic($id);

        if ($model->load(Yii::$app->request->post()) && $model->canUpdate(Yii::$app->user->model) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Запись обновлена');
<?php if ($createActionView) { ?>
            return $this->redirect(['view'<?= $redirectParamsString ?>, '<?= $pkParam ?>' => $model->primaryKey]);
<?php } elseif ($createActionIndex) { ?>
            return $this->redirect(['index'<?= $redirectParamsString ?>]);
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

    public function actionDelete(<?= count($requestFields) > 0 ? '$' . implode(', $', $requestFields) : '' ?>, $id)
    {
        $model = <?= $modelName ?>::findOrPanic($id);
        if ($model->canDelete(Yii::$app->user->model)) {
            $model->deleteOrPanic();
        } else {
            throw new ForbiddenHttpException();
        }
        return $this->redirect(['index'<?= $redirectParamsString ?>]);
    }
<?php } ?>

}

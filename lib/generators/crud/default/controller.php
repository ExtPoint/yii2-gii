<?php

namespace app\views;

use extpoint\yii2\gii\generators\crud\CrudGenerator;
use yii\web\View;

/* @var $this View */
/* @var $generator CrudGenerator */
/* @var $namespace string */
/* @var $className string */
/* @var $routePrefix string */
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

echo "<?php\n";
?>

namespace <?= $namespace ?>;

use Yii;
use app\core\base\AppController;
use <?= $modelClassName ?>;
<?php if ($createActionCreate && $withSearch) { ?>
use <?= $searchModelClassName ?>;
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
                        'url' => ['<?= $routePrefix ?>/update', ':id'],
                        'urlRule' => '<?= $url ?>/update/<id:\d+>',
                    ],
<?php } ?>
<?php if ($createActionView) { ?>
                    [
                        'label' => 'Просмотр',
                        'url' => ['<?= $routePrefix ?>/view', ':id'],
                        'urlRule' => '<?= $url ?>/<id:\d+>',
                    ],
<?php } ?>
<?php if ($withDelete) { ?>
                    [
                        'url' => ['<?= $routePrefix ?>/delete', ':id'],
                    ],
<?php } ?>
                ],
<?php } ?>
            ],
        ];
    }
<?php if ($createActionIndex && $withSearch) { ?>

    public function actionIndex()
    {
        $searchModel = new <?= $searchModelName ?>();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
<?php } ?>
<?php if ($createActionIndex && !$withSearch) { ?>

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelName ?>::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }
<?php } ?>
<?php if ($createActionView) { ?>

    public function actionView($id)
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
<?php if ($createActionView) { ?>

    public function actionCreate()
    {
        $model = new <?= $modelName ?>();
        if ($model->load(Yii::$app->request->post()) && $model->canCreate(Yii::$app->user->model) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Запись добавлена');
<?php if ($createActionView) { ?>
            return $this->redirect(['view', 'id' => $model->id]);
<?php } elseif ($createActionIndex) { ?>
            return $this->redirect(['index']);
<?php } else { ?>
            return $this->refresh();
<?php } ?>
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }
<?php } ?>
<?php if ($createActionView) { ?>

    public function actionUpdate($id)
    {
        $model = <?= $modelName ?>::findOrPanic($id);
        if ($model->load(Yii::$app->request->post()) && $model->canUpdate(Yii::$app->user->model) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Запись обновлена');
<?php if ($createActionView) { ?>
            return $this->redirect(['view', 'id' => $model->id]);
<?php } elseif ($createActionIndex) { ?>
            return $this->redirect(['index']);
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

    public function actionDelete($id)
    {
        $model = <?= $modelName ?>::findOrPanic($id);
        if ($model->canDelete(Yii::$app->user->model)) {
            $model->deleteOrPanic();
        } else {
            throw new ForbiddenHttpException();
        }
        return $this->redirect(['index']);
    }
<?php } ?>

}

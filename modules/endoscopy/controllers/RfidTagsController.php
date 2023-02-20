<?php

namespace app\modules\endoscopy\controllers;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;

use kartik\icons\Icon;
use kartik\grid\EditableColumnAction;

use app\modules\endoscopy\models\RfidTags;
use app\modules\endoscopy\models\search\RfidTagsSearch;

/**
 * RfidTagsController implements the CRUD actions for RfidTags model.
 */
class RfidTagsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [\app\modules\security\models\AuthItem::ROLE_Admin, /*\app\modules\security\models\AuthItem::ROLE_StaffAuthor*/],
                    ]
                ],
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all RfidTags models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new RfidTagsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new RfidTags model.
     * If creation is successful, the browser will be redirected to the 'search' page.
     * @return mixed
     */
    public function actionSearch()
    {
        $searchModel = new RfidTagsSearch();

        return $this->renderAjax('_search', [
            'model' => $searchModel,
        ]);
    }

    /**
     * Displays a single RfidTags model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Updates an existing RfidTags model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param null|integer $id
     * @param null|string $form
     * @param null|string $returnUrl
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id = null, $form = null, $returnUrl = null)
    {
        if (!is_null($id)) {
            $model = $this->findModel($id);
            $model->scenario = RfidTags::SCENARIO_UPDATE;
        } else {
            $model = new RfidTags(['scenario' => RfidTags::SCENARIO_CREATE]);
        }

        $post = \Yii::$app->request->post();

        /** Проверка модуля enableAjaxValidation */
        if (Yii::$app->request->isAjax && $model->load($post)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load($post)) {
            if ($valid = $model->validate()) {
                if ($model->save()) {
                    \Yii::$app->session->setFlash('success', '<h4>' . Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Successful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'The data is saved') . '.</p>');
                    // Вывести ссылку с которой пришел запрос или по умолчанию перейти на [index]
                    $returnUrl = Yii::$app->request->get('returnUrl', ['index']);
                    return $this->redirect($returnUrl);
                }
            }
            /*else {
                print_r($model->errors);
            }*/

            \Yii::$app->session->setFlash('error', '<h4>' . Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Unsuccessful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'The data is saved') . '.</p>');
        }

        /** Формирование формы ввода для \yii\bootstrap\Modal */
        if ($form === $model::FORM_TYPE_AJAX) {
            return $this->renderAjax('_form', [
                'model' => $model,
            ]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing RfidTags model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $returnUrl = Yii::$app->request->get('returnUrl', ['index']);

        $model = $this->findModel($id);

        if ($model->delete()) {
            \Yii::$app->session->setFlash('success', '<h4>' . Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Successful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'Selected item have been removed from the system') . '.</p>');
        } else {
            \Yii::$app->session->setFlash('error', '<h4>' . Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Unsuccessful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'Failed to delete the selected item') . '.</p>');
        }

        return $this->redirect($returnUrl);
    }

    /**
     * Finds the RfidTags model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return RfidTags the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = RfidTags::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}

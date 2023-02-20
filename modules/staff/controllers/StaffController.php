<?php

namespace app\modules\staff\controllers;


use Yii;

use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;

use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

use yii\bootstrap\ActiveForm;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;

use kartik\icons\Icon;
use kartik\grid\EditableColumnAction;

use app\models\Model;

use app\modules\security\models\AuthItem;
use app\modules\security\models\User;
use app\modules\staff\models\Phone;
use app\modules\staff\models\Staff;
use app\modules\staff\models\search\StaffSearch;

/**
 * StaffController implements the CRUD actions for Staff model.
 */
class StaffController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
//                        'roles' => ['Root-Admin', 'Администраторы'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Staff models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new StaffSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Updates an existing Staff model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param null|integer $id
     * @param null|string $form
     * @param null|string $returnUrl
     * @return mixed
     */
    public function actionUpdate($id = null, $form = null, $returnUrl = null)
    {
        if (empty($id)) {
            $model                  = new Staff(['scenario' => Staff::SCENARIO_ADD_STAFF]);
            $phonesForUser          = [new Phone()];

        } else {
            $model = $this->findModel($id);
            $model->scenario = Staff::SCENARIO_ADD_STAFF;
            $phonesForUser = (empty($model->phonesForUser)) ? [new Phone()] : $model->phonesForUser;

            foreach ($phonesForUser as $id => $val){
                // id for update
                $val->id = $val->id_phone;
            }

        }

        /** Проверка модуля enableAjaxValidation */
        if (\Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\bootstrap\ActiveForm::validate($model);
        }

        /**
         * ####  Обработка "Общих Данных" сотрудника  ####
         * @var $phonesForUser \app\modules\staff\models\Phone[]
         */
        if ($model->load(Yii::$app->request->post())) {

            $oldIDs = ArrayHelper::map($phonesForUser, 'id', 'id');
            $phonesForUser = Model::createMultiple(Phone::classname(), $phonesForUser);
            Model::loadMultiple($phonesForUser, Yii::$app->request->post());

            $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($phonesForUser, 'id', 'id')));

            $count_default_phone = 0;
            foreach ($phonesForUser as $id_new => $new){
                if($new->default_phone){ $count_default_phone++;}  // счетчик количества отмеченых телефонов по умолчанию
                if($new->id == null){   // обработка новых телефонов
                    $new->created_at = time();
                    $new->created_by = Yii::$app->user->identity->id;
                    $new->user_id = $model->id;
                }else{     // обработка обновленных телефонов
                    $new->updated_at = time();
                    $new->updated_by = Yii::$app->user->identity->id;
                }
            }

//      запись ошибки, если не отмечен общий номер телефона
            if($count_default_phone != 1){
                foreach ($phonesForUser as $val) {
                    $val->count_default_phone = true;
                }
            }

            // validate all models
            $valid = $model->validate();
            $valid = Model::validateMultiple($phonesForUser) && $valid;

            if ($valid) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    if ($flag = $model->save(false)) {
                        //  Удаление позиций
                        if (!empty($deletedIDs)) {
                            Phone::deleteAll(['id_phone' => $deletedIDs]);
                        }
                        // Добавление новых позиций
                        foreach ($phonesForUser as $phone) {

                            $phone->user_id = empty($model->user_id) ? $model->id : $phone->user_id; // проверка на отсутствие id сотрудника

                            if (!($flag = $phone->save(false))) {
                                $transaction->rollBack();
                                break;
                            }
                        }
                    }

                    if ($flag) {
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', '<h4>'.Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']).Yii::t('app','Successful').  '</h4> <p> '.Yii::t('app','The data is saved'). '.</p>');

                        $returnUrl = Yii::$app->request->get('returnUrl', ['index']);
                        return $this->redirect($returnUrl);
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                }
            }
/*            else{
                if($model->errors) echo '<pre>'.print_r($model->errors, true). '</pre>';
                if(is_array($phonesForUser)) echo '<pre>'.print_r(ArrayHelper::getColumn($phonesForUser,'errors'), true). '</pre>';
            }*/
        }

        /** Формирование формы ввода для \yii\bootstrap\Modal */
        if ($form === Staff::FORM_TYPE_AJAX) {
            return $this->renderAjax('_form', [
                'model'         => $model,
                'phonesForUser' => $phonesForUser,
            ]);
        }
        /** Формирование формы ввода для POST or GET */
        return $this->render('update', [
            'model'         => $model,
            'phonesForUser' => $phonesForUser,
        ]);
    }

    /**
     * Deletes an existing Staff model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
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
     *
     * Finds the Staff model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Staff the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Staff::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}

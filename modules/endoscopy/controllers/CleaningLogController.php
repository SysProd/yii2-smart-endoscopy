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

use app\modules\endoscopy\models\CleaningLog;
use app\modules\endoscopy\models\search\CleaningLogSearch;
use app\modules\endoscopy\models\CleaningLevelLog;
use app\modules\endoscopy\models\RfidTags;

/**
 * CleaningLogController implements the CRUD actions for CleaningLog model.
 */
class CleaningLogController extends Controller
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
                        'roles' => [\app\modules\security\models\AuthItem::ROLE_Admin, \app\modules\security\models\AuthItem::ROLE_StaffAuthor, \app\modules\security\models\AuthItem::ROLE_Device],
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
     * Lists all CleaningLog models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CleaningLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new CleaningLog model.
     * If creation is successful, the browser will be redirected to the 'search' page.
     * @return mixed
     */
    public function actionSearch()
    {
        $searchModel = new CleaningLogSearch();

        return $this->renderAjax('_search', [
            'model' => $searchModel,
        ]);
    }

    /**
     * Displays a single CleaningLog model.
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
     * Updates an existing CleaningLog model.
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
            $model->scenario = CleaningLog::SCENARIO_UPDATE;
        } else {
            $model = new CleaningLog(['scenario' => CleaningLog::SCENARIO_CREATE]);
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
            /*            else {
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
     * @param $rfidKey
     * @param $rfidStaff
     * @param null $form
     * @return bool|string|Response
     * @throws NotFoundHttpException
     */
//    public function actionAddKey($rfidKey,$rfidStaff, $form = null)
//    {
//        $modelStaff = $this->findModelRfid($rfidStaff); // Метка сотрудника
//        $modelKey = $this->findModelRfid($rfidKey); // Метка все что можно, кроме сотрудника
//
//        if(!$modelStaff->fx_staff && $modelKey->status_tied != $modelKey::Staff) { throw new NotFoundHttpException( \Yii::t('app', 'Нет сотрудника в запросе') ); }
//        if($modelKey->fx_staff && $modelKey->status_tied != $modelKey::No) { throw new NotFoundHttpException( \Yii::t('app', 'Нельзя передавать сотрудника') ); }
//
//        $transaction = \Yii::$app->db->beginTransaction();
//        try {
//
//            /**
//             * Мы узнаем какаой статус переданного в $modelKey
//             * #### Обновление и запись
//             * Находим последнюю текущую запись сотрудника
//             * Проверяем на каком этапе остановилась запись
//             * Проверяем соответствует ли ключ $modelKey следующему этапу записи
//             * Записываем следующий этап данные в CleaningLog и обновляем этам в CleaningLevelLog
//             * Записываем дату обновления в CleaningLog->updated_at
//             * Обновление записи в журнале "Логирование Очистки"
//             * @var $model \app\modules\endoscopy\models\CleaningLog
//             * @var $modelCleaningLevelLog \app\modules\endoscopy\models\CleaningLevelLog
//             * @var $attributeColumnNextLevel \app\modules\endoscopy\models\CleaningLevelLog
//             */
//            /*new \yii\db\Expression("CURDATE()")*/
//            if(($model = CleaningLog::findByAll()->where(['DATE_FORMAT(FROM_UNIXTIME(`add_data`),"%Y%d%m")' => date("Ydm"), 'staff_by' => $modelStaff->fx_staff, 'status_log' => CleaningLog::STATUS_ACTUAL])->one()) !== null){
//                echo 'Update Log';
//
//                /**
//                 * Поиск модели \app\modules\endoscopy\models\CleaningLevelLog
//                 */
//                if(($modelCleaningLevelLog = $model->cleaningLevelLog) === null){
//                    $transaction->rollBack();
//                    throw new \Exception(\Yii::t('app', 'model с уровнем журнала логирования отсутствует'));   // Присвоение ошибки
//                }
//
//                $modelCleaningLevelLog->status_rfid = $modelKey->status_tied; // Записать статус r-fid для журнала "Уроверь Очистки"
//
//                /**
//                 * Проверка статуса метки со статусом соответствующего лога в Журнале "Логирование Очистки"
//                 */
//                if($modelCleaningLevelLog->nextRfidStatus !== $modelKey->status_tied){
//                    $transaction->rollBack();
//                    throw new \Exception(\Yii::t('app', 'Нельза добавлять метку не соответствующего статуса!'));   // Присвоение ошибки
//                }
//
//                /**
//                 * Проверка наличия следующего атрибута для записи в Журнале "Уроверь Очистки"
//                 */
//                if(($attributeColumnNextLevel = $modelCleaningLevelLog->nextLogLevel) === null){
//                    $transaction->rollBack();
//                    throw new \Exception(\Yii::t('app', 'Атрибут модели не найден или уже сохранен!'));   // Присвоение ошибки
//                }
//
//                /**
//                 * Проверка статуса атрибута в Журнале "Уроверь Очистки"
//                 */
//                if($modelCleaningLevelLog->$attributeColumnNextLevel === $modelCleaningLevelLog::YES){
//                    $transaction->rollBack();
//                    throw new \Exception(\Yii::t('app', 'Статус уже изменен!'));   // Присвоение ошибки
//                }
//
//                $modelCleaningLevelLog->nextStatusColumn; // Изменить статус записи следующей колонки в журнале "Уроверь Очистки"
//                if($attributeColumnNextLevel === "level_2_test_1"){
//                    $model->test_tightness_by = $modelKey->id;
//                }elseif ($attributeColumnNextLevel === "level_3_clear_1" && empty($model->cleaning_agents_by)){
//                    $model->cleaning_agents_by = $modelKey->id;
//                    $model->cleaning_start = time();
//                }elseif ($attributeColumnNextLevel === "level_3_clear_1" && !empty($model->cleaning_agents_by)){
//                    $model->cleaning_end = time();
//                }elseif ($attributeColumnNextLevel === "level_4_test_clear_2" && empty($model->test_qualities_cleaning_status)){
//                    $model->test_qualities_cleaning_status = $modelKey->id;
//                    $model->test_qualities_cleaning_date = time();
//                }elseif ($attributeColumnNextLevel === "level_5_disinfection_manual" && empty($model->disinfection_manual_by)){
//                    $model->disinfection_type_by = $model::manual;
//                    $model->disinfection_manual_by = $modelKey->id;
//                    $model->disinfection_manual_start = time();
//                }elseif ($attributeColumnNextLevel === "level_5_disinfection_manual" && !empty($model->disinfection_manual_by)){
//                    $model->disinfection_manual_end = time();
//                    $model->status_log = $model::STATUS_COMPLETED;
//                }elseif ($attributeColumnNextLevel === "level_5_disinfection_auto" && empty($model->disinfection_auto_by) && empty($model->disinfection_auto_agents_by)){
//                    $model->disinfection_type_by = $model::auto;
//                    $model->disinfection_auto_by = $modelKey->id;
//                    $model->disinfection_auto_start = time();
//                }elseif ($attributeColumnNextLevel === "level_5_disinfection_auto" && !empty($model->disinfection_auto_by) && empty($model->disinfection_auto_agents_by)){
//                    $model->disinfection_auto_agents_by = $modelKey->id;
//                }elseif ($attributeColumnNextLevel === "level_5_disinfection_auto" && !empty($model->disinfection_auto_by) && !empty($model->disinfection_auto_agents_by)){
//                    $model->disinfection_auto_end = time();
//                    $model->status_log = $model::STATUS_COMPLETED;
//                }
//
//
//                if ($modelCleaningLevelLog->save()) { // Сохранить данные Логов в Журнале "Логирование Очистки"
//                    // Сохранить уроверь логов
//                    if (!$model->save()){
//                        echo '<pre>'.print_r($model->errors,true).'</pre>';
//                        $transaction->rollBack();
//                        throw new \Exception(\Yii::t('app', 'Ошибка сохрания в Журнале "Логирование Очистки"'));   // Присвоение ошибки
//                    }
//                }
//                else{
//                    echo '<pre>'.print_r($modelCleaningLevelLog->errors,true).'</pre>';
//                    $transaction->rollBack();
//                    throw new \Exception(\Yii::t('app', 'Ошибка сохрания в Журнале "Уроверь Очистки"'));   // Присвоение ошибки
//                }
//
////                echo '<pre>'.print_r($model,true).'</pre>';
//            }
//
//            /**
//             *              #### Новая запись
//             *            Записываем ключ сотрудника $modelStaff->fx_staff в CleaningLog->staff_by
//             *            Записываем ключ эндоскопа $modelKey->fx_tools в CleaningLog->tools_by
//             *            Записываем дату добавления и дату обновления в CleaningLog->add_data && CleaningLog->updated_at
//             * Создание записи в журнале "Логи Очистки"
//             * @var $model \app\modules\endoscopy\models\CleaningLog
//             * @var $modelLevel \app\modules\endoscopy\models\CleaningLevelLog
//             */
//            else{
////            echo 'Create  new';
////            echo '<pre>'.print_r($modelKey,true).'</pre>';
//                // Добавить Эндоскоп
//                if(empty($modelKey->fx_tools)){
//                        $transaction->rollBack();
//                        throw new \Exception(\Yii::t('app', 'Нельза добавлять метку отличную от эндоскопа return = false'));   // Присвоение ошибки
//                }
//                echo 'добавить в лог эндоскоп';
//                // Сохранить Лог
//                $model = new CleaningLog(['scenario' => CleaningLog::SCENARIO_CREATE]);
//                $date = time();
//                $model->add_data = $date;
//                $model->staff_by = $modelStaff->fx_staff;
//                $model->tools_by = $modelKey->id;
//                $model->updated_at = $date;
//
//                // Сохранить Уроверь выполнения логов
//                $modelLevel = new CleaningLevelLog(['scenario' => CleaningLevelLog::SCENARIO_CREATE]);
//
//                $modelLevel->add_data = $date;
//                $modelLevel->level_1_add = CleaningLevelLog::YES;
//                $modelLevel->staff_by = $modelStaff->fx_staff;
//                $modelLevel->updated_at = $date;
//
//                if ($model->save()) { // Сохранить Данные Логов
//                    $modelLevel->id = $model->id;
//                    // Сохранить уроверь логов
//                    if (!$modelLevel->save()){
//                        $transaction->rollBack();
//                        throw new \Exception(\Yii::t('app', 'Ошибка сохрания уровня логов'));   // Присвоение ошибки
//                    }
//                }
//                else{
//                    echo '<pre>'.print_r($model->errors,true).'</pre>';
//                    echo '<pre>'.print_r($modelLevel->errors,true).'</pre>';
//                    $transaction->rollBack();
//                    throw new \Exception(\Yii::t('app', 'Ошибка сохрания эндоскопа'));   // Присвоение ошибки
//                }
//
//            }
//
//            $transaction->commit();
//            return '<br> Данные сохранены!!';
//        } catch (\Exception $e) {
//            \Yii::$app->session->setFlash('error', '<h4>' . Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Unsuccessful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'The data is not saved') . '.</p>');
//            $transaction->rollBack();
//            return '<br> '.$e->getMessage();
//            return false;
//        }
//    }

    /**
     * Deletes an existing CleaningLog model.
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
     * Finds the CleaningLog model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CleaningLog the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CleaningLog::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * Finds the CleaningLog model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return RfidTags the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModelRfid($id)
    {
        if (($model = RfidTags::findOne(['coded_key' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}

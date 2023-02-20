<?php

namespace app\controllers;

use app\modules\endoscopy\models\CleaningLevelLog;
use app\modules\endoscopy\models\CleaningLog;
use app\modules\endoscopy\models\RfidTags;
use kartik\icons\Icon;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
//use app\models\LoginForm;
//use app\models\ContactForm;
use app\modules\security\models\LoginForm;
use app\modules\security\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['get','post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
/*            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],*/
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            // change layout for error action
            if ($action->id=='login')
                $this->layout = 'main-login';
            return true;
        } else {
            return false;
        }
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        // Перенаправлять всех пользователей с index на доступную страницу
        if(!Yii::$app->user->can(\app\modules\security\models\AuthItem::ROLE_Admin)){
            return $this->redirect(\yii\helpers\Url::toRoute(['/endoscopy/cleaning-log/index']));
        }
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Регистрация rfid меток в журнале "Логирование Очистки"
     * @param $rfidKey
     * @param $rfidStaff
     * @param null $form
     * @return bool|string|Response
     * @throws NotFoundHttpException
     */
    public function actionAddKey($rfidKey,$rfidStaff, $form = null)
    {
        $modelStaff = $this->findModelRfid($rfidStaff); // Метка сотрудника
        $modelKey = $this->findModelRfid($rfidKey); // Метка все что можно, кроме сотрудника

        if($rfidStaff !== $rfidKey && ($modelStaff->status_tied != $modelStaff::Staff || $modelKey->status_tied == $modelKey::Staff) ){throw new NotFoundHttpException( \Yii::t('app', 'Создайте верный запрос') );}
        if($rfidStaff === $rfidKey && $modelStaff->status_tied != $modelStaff::Staff && $modelKey->status_tied != $modelKey::Staff ){throw new NotFoundHttpException( \Yii::t('app', 'Создайте верный запрос') );}
//        if(!$modelStaff->fx_staff && $modelKey->status_tied != $modelKey::Staff) { throw new NotFoundHttpException( \Yii::t('app', 'Нет сотрудника в запросе') ); }
//        if($modelKey->fx_staff && $modelKey->status_tied != $modelKey::No) { throw new NotFoundHttpException( \Yii::t('app', 'Нельзя передавать сотрудника') ); }

        $transaction = \Yii::$app->db->beginTransaction();
        try {

            /**
             * Мы узнаем какаой статус переданного в $modelKey
             * #### Обновление и запись
             * Находим последнюю текущую запись сотрудника
             * Проверяем на каком этапе остановилась запись
             * Проверяем соответствует ли ключ $modelKey следующему этапу записи
             * Записываем следующий этап данные в CleaningLog и обновляем этам в CleaningLevelLog
             * Записываем дату обновления в CleaningLog->updated_at
             * Обновление записи в журнале "Логирование Очистки"
             * @var $model \app\modules\endoscopy\models\CleaningLog
             * @var $modelCleaningLevelLog \app\modules\endoscopy\models\CleaningLevelLog
             * @var $attributeColumnNextLevel \app\modules\endoscopy\models\CleaningLevelLog
             */
            /*new \yii\db\Expression("CURDATE()")*/
            if(($model = CleaningLog::findByAll()->where(['DATE_FORMAT(FROM_UNIXTIME(`add_data`),"%Y%d%m")' => date("Ydm"), 'staff_by' => $modelStaff->fx_staff, 'status_log' => CleaningLog::STATUS_ACTUAL])->one()) !== null){
                echo 'Update Log';

                /**
                 * Поиск модели \app\modules\endoscopy\models\CleaningLevelLog
                 */
                if(($modelCleaningLevelLog = $model->cleaningLevelLog) === null){
                    $transaction->rollBack();
                    throw new \Exception(\Yii::t('app', 'model с уровнем журнала логирования отсутствует'));   // Присвоение ошибки
                }

                $modelCleaningLevelLog->status_rfid = $modelKey->status_tied; // Записать статус r-fid для журнала "Уроверь Очистки"

                /**
                 * Проверка статуса метки со статусом соответствующего лога в Журнале "Логирование Очистки"
                 */
                if($modelCleaningLevelLog->nextRfidStatus !== $modelKey->status_tied){
                    $transaction->rollBack();
                    throw new \Exception(\Yii::t('app', 'Нельза добавлять метку не соответствующего статуса!'));   // Присвоение ошибки
                }

                /**
                 * Проверка наличия следующего атрибута для записи в Журнале "Уроверь Очистки"
                 */
                if(($attributeColumnNextLevel = $modelCleaningLevelLog->nextLogLevel) === null){
                    $transaction->rollBack();
                    throw new \Exception(\Yii::t('app', 'Атрибут модели не найден или уже сохранен!'));   // Присвоение ошибки
                }

                /**
                 * Проверка статуса атрибута в Журнале "Уроверь Очистки"
                 */
                if($modelCleaningLevelLog->$attributeColumnNextLevel === $modelCleaningLevelLog::YES){
                    $transaction->rollBack();
                    throw new \Exception(\Yii::t('app', 'Статус уже изменен!'));   // Присвоение ошибки
                }

                $modelCleaningLevelLog->nextStatusColumn; // Изменить статус записи следующей колонки в журнале "Уроверь Очистки"
                if($attributeColumnNextLevel === "level_1_add_tools_2"){
                    $model->tools_by = $modelKey->id;
                }elseif($attributeColumnNextLevel === "level_2_test_1"){
                    $model->test_tightness_by = $modelKey->id;
                }elseif ($attributeColumnNextLevel === "level_3_clear_1" && empty($model->cleaning_agents_by)){
                    $model->cleaning_agents_by = $modelKey->id;
                    $model->cleaning_start = time();
                }elseif ($attributeColumnNextLevel === "level_3_clear_1" && !empty($model->cleaning_agents_by)){
                    $model->cleaning_end = time();
                }elseif ($attributeColumnNextLevel === "level_4_test_clear_2" && empty($model->test_qualities_cleaning_status)){
                    $model->test_qualities_cleaning_status = $modelKey->id;
                    $model->test_qualities_cleaning_date = time();
                }elseif ($attributeColumnNextLevel === "level_5_disinfection_manual" && empty($model->disinfection_manual_by)){
                    $model->disinfection_type_by = $model::manual;
                    $model->disinfection_manual_by = $modelKey->id;
                    $model->disinfection_manual_start = time();
                }elseif ($attributeColumnNextLevel === "level_5_disinfection_manual" && !empty($model->disinfection_manual_by)){
                    $model->disinfection_manual_end = time();
                    $model->status_log = $model::STATUS_COMPLETED;
                }elseif ($attributeColumnNextLevel === "level_5_disinfection_auto" && empty($model->disinfection_auto_by) && empty($model->disinfection_auto_agents_by)){
                    $model->disinfection_type_by = $model::auto;
                    $model->disinfection_auto_by = $modelKey->id;
                    $model->disinfection_auto_start = time();
                }elseif ($attributeColumnNextLevel === "level_5_disinfection_auto" && !empty($model->disinfection_auto_by) && empty($model->disinfection_auto_agents_by)){
                    $model->disinfection_auto_agents_by = $modelKey->id;
                }elseif ($attributeColumnNextLevel === "level_5_disinfection_auto" && !empty($model->disinfection_auto_by) && !empty($model->disinfection_auto_agents_by)){
                    $model->disinfection_auto_end = time();
                    $model->status_log = $model::STATUS_COMPLETED;
                }


                if ($modelCleaningLevelLog->save()) { // Сохранить данные Логов в Журнале "Логирование Очистки"
                    // Сохранить уроверь логов
                    if (!$model->save()){
                        echo '<pre>'.print_r($model->errors,true).'</pre>';
                        $transaction->rollBack();
                        throw new \Exception(\Yii::t('app', 'Ошибка сохрания в Журнале "Логирование Очистки"'));   // Присвоение ошибки
                    }
                }
                else{
                    echo '<pre>'.print_r($modelCleaningLevelLog->errors,true).'</pre>';
                    $transaction->rollBack();
                    throw new \Exception(\Yii::t('app', 'Ошибка сохрания в Журнале "Уроверь Очистки"'));   // Присвоение ошибки
                }

//                echo '<pre>'.print_r($model,true).'</pre>';
            }

            /**
             *              #### Новая запись
             *            Записываем ключ сотрудника $modelStaff->fx_staff в CleaningLog->staff_by
             *            Записываем ключ эндоскопа $modelKey->fx_tools в CleaningLog->tools_by
             *            Записываем дату добавления и дату обновления в CleaningLog->add_data && CleaningLog->updated_at
             * Создание записи в журнале "Логи Очистки"
             * @var $model \app\modules\endoscopy\models\CleaningLog
             * @var $modelLevel \app\modules\endoscopy\models\CleaningLevelLog
             */
            else{
            echo 'Create  new';
//            echo '<pre>'.print_r($modelKey,true).'</pre>';
                // Добавить Эндоскоп
                if(empty($modelKey->fx_staff)){
                    $transaction->rollBack();
                    throw new \Exception(\Yii::t('app', 'Нельза добавлять метку отличную от сотрудника'));   // Присвоение ошибки
                }
                // Сохранить Лог
                $model = new CleaningLog(['scenario' => CleaningLog::SCENARIO_CREATE]);

                $date = time();
                $model->add_data = $date;
                $model->staff_by = $modelStaff->fx_staff;
                $model->updated_at = $date;

                // Сохранить Уроверь выполнения логов
                $modelLevel = new CleaningLevelLog(['scenario' => CleaningLevelLog::SCENARIO_CREATE]);

                $modelLevel->add_data = $date;
                $modelLevel->level_1_add_staff_1 = CleaningLevelLog::YES;
                $modelLevel->staff_by = $modelStaff->fx_staff;
                $modelLevel->updated_at = $date;

                if ($model->save()) { // Сохранить Данные Логов
                    $modelLevel->id = $model->id;
                    // Сохранить уроверь логов
                    if (!$modelLevel->save()){
                        $transaction->rollBack();
                        throw new \Exception(\Yii::t('app', 'Ошибка сохрания уровня логов'));   // Присвоение ошибки
                    }
                }
                else{
                    echo '<pre>'.print_r($model->errors,true).'</pre>';
                    echo '<pre>'.print_r($modelLevel->errors,true).'</pre>';
                    $transaction->rollBack();
                    throw new \Exception(\Yii::t('app', 'Ошибка сохрания сотрудника'));   // Присвоение ошибки
                }

            }

            $transaction->commit();
            return '<br> Данные сохранены!!';
        } catch (\Exception $e) {
            \Yii::$app->session->setFlash('error', '<h4>' . Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Unsuccessful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'The data is not saved') . '.</p>');
            $transaction->rollBack();
            return '<br> '.$e->getMessage();
            return false;
        }
    }

    /**
     * Свой обработчик ошибок
     * @return string
     */
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;

        if ($exception !== null) {
            $statusCode = $exception->statusCode;
            $name = $exception->getName();
            $message = $exception->getMessage();

            $this->layout = 'main-login';

//            return $this->render('errorDefault', [
//            return $this->render('errorNoSignal', [
            return $this->render('error', [
                'exception' => $exception,      // Массив всех данных ошибки
                'statusCode' => $statusCode,    // Номер ошибки
                'name' => \Yii::t('yii',$name), // Название ошибки
                'message' => $message           // Развернутое сообщение об ошибке
            ]);
        }
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

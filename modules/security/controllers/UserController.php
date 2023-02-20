<?php

namespace app\modules\security\controllers;

use Yii;

use app\models\Model;

use yii\db\Query;

use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

use yii\widgets\MaskedInput;
use yii\widgets\MaskedInputAsset;

use yii\bootstrap\ActiveForm;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

use kartik\icons\Icon;

use app\modules\staff\models\Staff;
use app\modules\security\models\User;
use app\modules\security\models\AuthItem;
use app\modules\security\models\ResetPasswordAdmin;
use app\modules\security\models\UserProfile;
use app\modules\security\models\search\UserSearch;

/**
 * UserController implements the CRUD actions for User model.
 * Class UserController
 * @package app\modules\security\controllers
 */
class UserController extends Controller
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
                        'roles' => [AuthItem::ROLE_Admin],
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
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new ShopStuff model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionSearch()
    {
        $searchModel = new UserSearch();

        return $this->renderAjax('_search', [
            'model' => $searchModel,
        ]);
    }

    /**
     * Добавление нового пользователя в систему
     * @param $id
     * @param null $form
     * @return array|string|Response
     * @throws NotFoundHttpException
     */
    public function actionReg($id, $form = null)
    {

        /** ######################################################################
         *  Существует ошибка при добавлении роли добавляется роль Администратор (надо решить)
         * ######################################################################
         */
        if(\Yii::$app->user->can(AuthItem::OPR_CreateUser) //    Проверка разрешения доступа к странице
            && Staff::findOne($id)                         //    Проверка, существует ли пришедший профиль
        ) {
            if( !(Staff::checkRegStaff($id)) ) {    //    Проверка, зарегистрированный ли этот сотрудник
                $user = new User();
                $user->scenario = User::SCENARIO_ADD_USER;

                $profile = Staff::findOne($id);

                $user->id = $id;
                $user->confirmed_reg = \Yii::$app->user->identity->id;
                $user->email = (isset($profile->email)) ? $profile->email : $user->email;

                /** Проверка модуля enableAjaxValidation */
                if (\Yii::$app->request->isAjax && $user->load(Yii::$app->request->post())) {
                    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return \yii\bootstrap\ActiveForm::validate($user);
                }

                if ($user->load(\Yii::$app->request->post())) {
                    $user->setPassword($user->password);    // Создать хэш пароля
                    $user->generateAuthKey();               // Создать ключ авторизации

                    if ( $valid = $user->validate() ) {
                        $transaction = \Yii::$app->db->beginTransaction();
                        try {
                            if ($flag = $user->save()) {
                                $roles = isset(\Yii::$app->request->post('User')['role']) ? \Yii::$app->request->post('User')['role'] : null;
                                //  Запись ролей в бд
                                if (!empty($roles)) {
                                    $user->role = $roles;

                                    // Проверка на наличие ошибок при сохранении роли пользователя
                                    if (!($user->createRoles()) && strlen($user->getErrorMessage()) > 0) {
//                                        \Yii::$app->session->addFlash('error', $user->getErrorMessage());
                                        $transaction->rollBack();
                                        throw new \Exception($user->getErrorMessage());   // Присвоение ошибки
                                    }
//                                    var_dump($user->role);
//                                    die();
                                }
                            }
                            if ($flag) {
                                if($user->send_mail){ $user->sendEmail(); }     // отправить письмо с уведомлением о регистрации в системе
                                \Yii::$app->session->addFlash('success', '<h4>'.Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Successful').  '</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app','{nameType} <strong>{fullName}</strong> registered in the system',    ['nameType' => \Yii::t('app', 'The employee'), 'fullName' => Html::encode($user->userProfile->fullName)] ).'.</p>');
                                $transaction->commit();
                                return $this->redirect(['index']);
                            }
                        } catch (\Exception $e) {
                            \Yii::$app->session->addFlash('error',   '<h4>'.Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Unsuccessful').'</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app','{nameType} <strong>{fullName}</strong> not registered in the system',['nameType' => \Yii::t('app', 'The employee'), 'fullName' => Html::encode($user->userProfile->fullName)] ).'.</p>');
//                            print_r($e->getMessage());
//                            die();
                            $transaction->rollBack();
                            return $this->redirect(['reg', 'id' => $id]);
                        }
                    }

                }

                $items = $user->arrayListRole;


                /** Формирование формы ввода для \yii\bootstrap\Modal */
                if ($form === Staff::FORM_TYPE_AJAX) {
                    return $this->renderAjax('_form', [
                        'model' => $user,
                        'role' => [],
                        'items' => $items,
                    ]);
                }
                /** Формирование формы ввода для POST or GET */
                return $this->render('create', [
                    'model' => $user,
                    'role' => [],
                    'items' => $items,
                ]);

/*                return $this->render('create', [
                    'model' => $user,
                    'role' => [],
                    'items' => $items,
                ]);*/
            }
                return $this->redirect(['update', 'id' => $id]);

        }else{
            throw new NotFoundHttpException( \Yii::t('app', 'Page not found or are restricted for you.') );
        }

    }

    /**
     * Добавление нового пользователя в систему
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User(['scenario' => User::SCENARIO_ADD_USER]);

        // Проверка модуля enableAjaxValidation
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(\Yii::$app->request->post())) {

            if ($valid = $model->validate()) {

                $transaction = \Yii::$app->db->beginTransaction();
                try {

                    $model->setPassword($model->password);    // Создать хэш пароля
                    $model->generateAuthKey();                // Создать ключ авторизации

                    if ($flag = $model->save()) {

                        $roles = isset(\Yii::$app->request->post('User')['role']) ? \Yii::$app->request->post('User')['role'] : null;
                        $auth = Yii::$app->getAuthManager();

                        // Проверка, если пользователь без роли "ROLE_Admin" или нет выбранных ролей.
                        // Присвоение роли к пользователю средствами Yii2
                        // По-умолчанию назначить роль "ROLE_StaffAuthor"
                        if(!Yii::$app->user->can(AuthItem::ROLE_Admin) or empty($roles)){
                            $auth->assign($auth->getRole(AuthItem::ROLE_StaffAuthor), $model->id);
                        }elseif(!empty($roles)){
                            foreach($roles as $val){ $auth->assign($auth->getRole($val), $model->id); }
                        }
                    }
                    if ($flag) {

                        if($model->send_mail){ $model->sendEmail(); }     // отправить письмо с уведомлением о регистрации в системе
                        \Yii::$app->session->setFlash('success', '<h4>' . Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Successful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'The data is saved') . '.</p>');
                        $transaction->commit();

                        return $this->redirect(
                            [
                                'update',
                                'id' => $model->id,
                                'tab' => \Yii::$app->request->get('tab')
                            ]
                        );
                    }
                } catch (\Exception $e) {
                    \Yii::$app->session->setFlash('error', '<h4>' . Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Unsuccessful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'When processing your request an error occurred.') . '</p>');
                    $transaction->rollBack();
                    $items = $model->arrayListRole;
                    return $this->redirect(['index']);
                }

            }
        }
            $items = $model->arrayListRole;

            return $this->renderAjax('_form', [
                'model' => $model,
                'role' => [],
                'items' => $items,
            ]);

    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        // Аутентификационные данные пользователя
        $users              = $this->findModel($id);
        $users->scenario    = User::SCENARIO_UPDATE_USER;

        // Профиль пользователя
        $profiles   = empty($users->userProfile) ? new UserProfile(['id' => $users->id]) : $users->userProfile;


        /**
         * ####  Обработка "Профиля" пользователя  ####
         */
        if ($profiles->load(\Yii::$app->request->post())) {

            if ( $valid = $profiles->validate() ) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    if ($profiles->save()) {
                        \Yii::$app->session->setFlash('success', '<h4>'.Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Successful').  '</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app','The data is saved').'.</p>');
                        $transaction->commit();

                        switch (\Yii::$app->request->post('action', 'save')) {
                            case 'next':
                                return $this->redirect(
                                    [
                                        'index',
                                    ]
                                );
                            default:
                                return $this->redirect(
                                    [
                                        'update',
                                        'id' => $users->id,
                                        'tab' => \Yii::$app->request->get('tab')
                                    ]
                                );
                        }

                    }
                } catch (\Exception $e) {
                    \Yii::$app->session->setFlash('error',   '<h4>'.Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Unsuccessful').'</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app','The data is not saved').'.</p>');
                    $transaction->rollBack();
                    return $this->redirect(['update', 'id' => $id]);
                }

            }

        }

        /**
         * ####  Обработка "Аутентификационные Данных" пользователя  ####
         */
        if ($users->load(\Yii::$app->request->post())) {

            if ( $valid = $users->validate() ) {

                $transaction = \Yii::$app->db->beginTransaction();
                try {

                    if ($flag = $users->save()) {
                        $roles = isset(\Yii::$app->request->post('User')['role']) ? \Yii::$app->request->post('User')['role'] : null;
                        $rolesDB = $users->authAssignments;

                        //  Запись ролей в бд
                        if ($roles != null or !empty($rolesDB)) {
                            $users->role = $roles;
                            $users->updateRoles();
                            if (strlen($users->getErrorMessage()) > 0) {
                                $transaction->rollBack();
                                throw new \Exception($users->getErrorMessage());   // Присвоение ошибки
                            }
                        }
                    }
                    if ($flag) {
                        \Yii::$app->session->setFlash('success', '<h4>'.Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Successful').  '</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app','The data is saved').'.</p>');
                        $transaction->commit();

                        switch (\Yii::$app->request->post('action', 'save')) {
                            case 'next':
                                return $this->redirect(
                                    [
                                        'index',
                                    ]
                                );
                            default:
                                return $this->redirect(
                                    [
                                        'update',
                                        'id' => $users->id,
                                        'tab' => \Yii::$app->request->get('tab')
                                    ]
                                );
                        }

                    }
                } catch (\Exception $e) {
                    \Yii::$app->session->setFlash('error',   '<h4>'.Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Unsuccessful').'</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app','When processing your request an error occurred.').'.</p>');
                    $transaction->rollBack();
                    return $this->redirect(['update', 'id' => $id]);
                }

            }

        }

            $items = $users->arrayListRole;
            $roles = $users->authAssignments;
            $selected = [];

            foreach ($roles as $role) {
                $selected[] = $role->item_name;
            }
            return $this->render('update', [
                'model' => $users,
                'role' => $selected,
                'items' => $items,
                'profile' => $profiles,
            ]);
    }

    /**
     * Форма сброса пароля
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionReset($id)
    {

        $this->findModel($id);  // для проверки доступа к странице

        $model = ResetPasswordAdmin::findOne($id);
        $model->scenario = User::SCENARIO_RESET_PASS;

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            // проверка на авто-генерацию пароля
            if($model->auto_generate){ $model->password = $model->generatePassword(10); }

            $model->setPassword($model->password);    // Создать хэш пароля
            $model->generateAuthKey();                // Создать ключ авторизации

            if($model->save()){
//                Yii::$app->session->destroy(); // Удаление всех сессий пользователя
//                Yii::$app->db->createCommand()->delete(Yii::$app->session, ['user_id' => $model->id])->execute(); // Удаление всех сессий пользователя
                if($model->send_mail){ $model->sendEmailForReset(); } // отправить письмо с уведомлением о смене пароля
                Yii::$app->session->setFlash('success', '<h4>'.Icon::show('check-circle', ['class' => 'fa kv-alert-title']).\Yii::t('app','Successful').'</h4><hr class="kv-alert-separator"/><p>'.\Yii::t('app','Changing data of «{attribute}»: <strong>{fullName}</strong>.',['attribute' => \Yii::t('app', 'user'), 'fullName' => Html::encode($model->username)]).'</p>');
            }else{
                Yii::$app->session->setFlash('error',   '<h4>'.Icon::show('times-circle', ['class' => 'fa kv-alert-title']).\Yii::t('app','Unsuccessful').'</h4><hr class="kv-alert-separator"/><p>'.\Yii::t('app', 'Changing data of «{attribute}»: <strong>{fullName}</strong>.', ['attribute' => \Yii::t('app', 'user'), 'fullName' => Html::encode($model->username)]).'.</p>');
            }

            return $this->redirect(['index']);

        }else{
            return $this->renderAjax('resetPass', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param $id
     * @return \yii\web\Response|mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {

        $user = $this->findModel($id);
        $fullName = isset($user->userProfile) ? $user->userProfile->fullName : $user->username;

        /**
         * Разрешить полное удаление "Пользователей" для Операции "deleteUser"
         */
        if(\Yii::$app->user->can(AuthItem::OPR_DeleteUser)) {
            if ($user->delete()) {
                \Yii::$app->session->setFlash('success', '<h4>'.Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Successful').  '</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app','{nameType} <strong>{fullName}</strong> removed from the system',    ['nameType' => \Yii::t('app', 'The User'), 'fullName' => Html::encode($fullName)]).'.</p>');
            } else {
                \Yii::$app->session->setFlash('error',   '<h4>'.Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Unsuccessful').'</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app','{nameType} <strong>{fullName}</strong> not removed from the system',['nameType' => \Yii::t('app', 'The User'), 'fullName' => Html::encode($fullName)]).'.</p>');
            }

        /**
         * Разрешить удаление "Пользователей" для Операции "deleteUser" с переданными параметрами "post"
         */
        }elseif(\Yii::$app->user->can(AuthItem::OPR_DeleteUser, ['post' => $user]) && $user->status_system !== User::STATUS_SYSTEM_DELETED){
            $user->status_system = User::STATUS_SYSTEM_DELETED;
            if ($user->save(false,  ['status_system'])) {
                \Yii::$app->session->setFlash('success', '<h4>'.Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Successful').  '</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app','{nameType} <strong>{fullName}</strong> removed from the system',    ['nameType' => \Yii::t('app', 'The User'), 'fullName' => Html::encode($fullName)]).'.</p>');
            } else {
                \Yii::$app->session->setFlash('error',   '<h4>'.Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Unsuccessful').'</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app','{nameType} <strong>{fullName}</strong> not removed from the system',['nameType' => \Yii::t('app', 'The User'), 'fullName' => Html::encode($fullName)]).'.</p>');
            }
        }else{
            throw new NotFoundHttpException( \Yii::t('app', 'Page not found or are restricted for you.') );
        }

        return $this->redirect(['index']);

    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        /**
         * @var $model \app\modules\security\models\User
         */
        $model = User::findOne($id);

        if($model === null) {
        // При неудачном запросе вывести ошибку
            throw new NotFoundHttpException(\Yii::t('app', 'Page not found.'));
        }elseif(!Yii::$app->user->can(AuthItem::OPR_UpdateUser, ['post' => $model])){
        // Запретить выводить "Пользователя" недоступного для этой "Операции"
            throw new NotFoundHttpException( \Yii::t('app', 'Page not found or are restricted for you.') );
        }elseif($model->status_system === User::STATUS_SYSTEM_DELETED && Yii::$app->user->can(AuthItem::OPR_UpdateUser, ['post' => $model]) && !Yii::$app->user->can(AuthItem::OPR_UpdateUser)){
        // Запретить выводить "Пользователя" со статусом "Удаленный" для всех, кроме "Администраторов"
            throw new NotFoundHttpException( \Yii::t('app', 'Page not found.') );
        }

        return $model;
    }
}

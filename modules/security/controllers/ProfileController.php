<?php

namespace app\modules\security\controllers;

use Yii;

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
class ProfileController extends Controller
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
                        'roles' => [AuthItem::ROLE_Admin, AuthItem::ROLE_StaffAuthor],
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
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        // Аутентификационные данные пользователя
        $users               = $this->findModel($id);
        $users->scenario     = User::SCENARIO_UPDATE_USER;

        // Сброс пароля
        $resetPass           = ResetPasswordAdmin::findOne($id);
        $resetPass->scenario = User::SCENARIO_RESET_PASS;

        // Профиль пользователя
        $profiles   = empty($users->userProfile) ? new UserProfile(['id' => $users->id]) : $users->userProfile;

        /**
         * ####  Обработка "Сброса пароля" пользователя  ####
         */
        if ($resetPass->load(\Yii::$app->request->post())) {


            if ( $valid = $resetPass->validate() ) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    // проверка на авто-генерацию пароля
                    if ($resetPass->auto_generate) {
                        $resetPass->password = $resetPass->generatePassword(10);
                    }

                    $resetPass->setPassword($resetPass->password);  // Создать хэш пароля
                    $resetPass->generateAuthKey();                  // Создать ключ авторизации

                    if ($resetPass->save()) {
                        Yii::$app->db->createCommand()->delete(Yii::$app->session->sessionTable, ['user_id' => $resetPass->id])->execute(); // Удаление всех сессий пользователя
                        if ($resetPass->send_mail) {
                            $resetPass->sendEmailForReset();
                        } // отправить письмо с уведомлением о смене пароля

                        \Yii::$app->session->setFlash('success', '<h4>' . Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Successful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'The data is saved') . '.</p>');
                        $transaction->commit();

                        return $this->redirect(['update', 'id' => $id]);
                    }
                } catch (\Exception $e) {
                    \Yii::$app->session->setFlash('error', '<h4>' . Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Unsuccessful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'The data is not saved') . '.</p>');
                    $transaction->rollBack();
                    return $this->redirect(['update', 'id' => $id]);
                }
            }

        }

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

                        return $this->redirect(['update', 'id' => $id]);
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

                    if ($users->save()) {
                        \Yii::$app->session->setFlash('success', '<h4>'.Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Successful').  '</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app','The data is saved').'.</p>');
                        $transaction->commit();

                        return $this->redirect(['update', 'id' => $id]);

                    }
                } catch (\Exception $e) {
                    \Yii::$app->session->setFlash('error',   '<h4>'.Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Unsuccessful').'</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app','When processing your request an error occurred.').'.</p>');
                    $transaction->rollBack();
                    return $this->redirect(['update', 'id' => $id]);
                }

            }

        }

             return $this->render('update', [
                'model'     => $users,
                'profile'   => $profiles,
                'resetPass' => $resetPass,
            ]);
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

        if(\Yii::$app->user->can(AuthItem::OPR_UpdateProfile, ['post' => $user])){
            $user->status_system = User::STATUS_SYSTEM_DELETED;
            if ($user->save(false,  ['status_system'])) {
                \Yii::$app->session->setFlash('success', '<h4>'.Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Successful').  '</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app', 'Account deleting').'</p>');
            } else {
                \Yii::$app->session->setFlash('error',   '<h4>'.Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']).\Yii::t('app','Unsuccessful').'</h4><hr class="kv-alert-separator" /><p> '.\Yii::t('app', 'Account deleting').'</p>');
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

        // При неудачном запросе вывести ошибку
        if($model === null)
        {
            throw new NotFoundHttpException(\Yii::t('app', 'Page not found.'));
        }

        // Запретить выводить "Профиль" недоступного для роли "Авторы"
        elseif( !Yii::$app->user->can(AuthItem::OPR_UpdateProfile, ['post' => $model]) or $model->status_system === User::STATUS_SYSTEM_DELETED )
        {
            throw new NotFoundHttpException( \Yii::t('app', 'Page not found.') );
        }

        return $model;
    }
}

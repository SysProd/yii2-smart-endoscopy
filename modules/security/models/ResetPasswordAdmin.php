<?php

namespace app\modules\security\models;

use Yii;
use yii\base\Model;
use yii\base\InvalidParamException;
use app\modules\security\models\User;

/**
 * Password reset form
 *
 * @property string $password
 * @property string $password_repeat
 * @property boolean $send_mail
 * @property boolean $auto_generate
 * @property string $require_change
 */
class ResetPasswordAdmin extends User
{

    /**
     * Сценарии использования модуля "ResetPasswordAdmin"
     */
    const   SCENARIO_RESET_PASS   = 'reset';

    public  $password,                   // пароль
            $password_repeat,            // повторный пароль
            $auto_generate = false,      // авто-генерация пароля
            $send_mail = false;          // отправка email


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['auto_generate'], 'required', 'on' => self::SCENARIO_RESET_PASS, ],
            [['password_repeat'], 'compare', 'compareAttribute' => 'password', 'message' => \Yii::t('app','New passwords do not match')],
            [['send_mail'], 'boolean'],
            [['auto_generate'], 'validateAutoGen'],
            [['send_mail'],      'default', 'value' => false ],
            [['password'], 'string', 'min' => 6, 'max' => 100],
            [['password_repeat'], 'string', 'min' => 6, 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'password'           => \Yii::t('app', 'New Password'),
            'password_repeat'    => \Yii::t('app', 'New Password Confirm'),
            'auto_generate'      => \Yii::t('app', 'Auto-generate password'),
            'send_mail'          => \Yii::t('app', 'Send to mail'),
        ];
    }

    /**
     * Функция проверки ввода данных
     * зависит от выбранного "auto_generate"
     */
    public function validateAutoGen($attribute, $params)
    {
        if(!$this->$attribute)
        {
            if(empty($this->password)){
                $this->addError('password', Yii::t('app', 'Enter the «{attribute}».', ['attribute'=>$this->getAttributeLabel('password')]));
            }
            if(empty($this->password_repeat)){
                $this->addError('password_repeat', Yii::t('app', 'Enter the «{attribute}».', ['attribute'=>$this->getAttributeLabel('password_repeat')]));
            }
        }else{
            $this->send_mail = true;
        }
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean whether the email was send
     */
    public function sendEmailForReset()
    {
//        if ($this->email) {
///*            $sendMail = new MailerForm();
//
//            $sendMail->fromEmail = \Yii::$app->params['supportEmail'];
//            $sendMail->fromName = \Yii::$app->name . ' robot';
//            $sendMail->toEmail = $this->email;
//            $sendMail->subject = \Yii::t('app', 'Resetting the account password');
//            $sendMail->subject = \Yii::t('app', 'Resetting the account password');
//            $sendMail->view = ['html' => 'userPasswordReset-html'];
//            $sendMail->arrayParams = ['model' => $this];
//
//            $sendMail->sendEmail;*/
//            Yii::$app
//                ->mailer
//                ->compose(
//                    ['html' => 'userPasswordReset-html'],
//                    ['model' => $this]
//                )
//                ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' robot'])
//                ->setTo($this->email)
//                ->setSubject(\Yii::t('app', 'Resetting the account password'))
//                ->send();
//
//            return true;
//        }

        if ($this->email) {
             Yii::$app
                ->mailer
                ->compose(
                    ['html' => 'userPasswordReset-html'],
                    ['model' => $this]
                )
                ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' robot'])
                ->setTo($this->email)
                ->setSubject(\Yii::t('app', 'Resetting the account password'))
                ->send();

            return true;
        }

        return false;
    }

}

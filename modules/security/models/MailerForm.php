<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 26.06.17
 * Time: 14:53
 */

namespace app\modules\security\models;

use Yii;
use yii\base\Model;

/**
 * Class MailerForm
 * @package app\modules\security\models
 *
 * @property string $fromEmail
 * @property string $toEmail
 * @property string $fromName
 * @property string $subject
 * @property string $body
 * @property string $view
 * @property array $arrayParams
 *
 * @property MailerForm $sendEmail
 */
class MailerForm extends Model
{
    public $fromEmail;
    public $toEmail;
    public $fromName;
    public $subject;
    public $body = null;
    public $view = null;
    public $arrayParams = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fromEmail', 'fromName', 'toEmail', 'subject', 'body'], 'required'],

            [['fromEmail',], 'email'],
            [['toEmail',], 'email'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fromEmail'     => \Yii::t('app', 'From'),
            'fromName'      => \Yii::t('app', 'From name'),
            'toEmail'       => \Yii::t('app', 'To'),
            'subject'       => \Yii::t('app', 'Subject of the letter'),
            'body'          => \Yii::t('app', 'Body of the letter'),
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     * @return boolean whether the email was send
     */
    public function getSendEmail()
    {
        if ($this->validate()) {
            Yii::$app->mailer->compose($this->view, $this->arrayParams)
                ->setTo($this->toEmail)
                ->setFrom([$this->fromEmail => $this->fromName])
                ->setSubject($this->subject)
                ->setTextBody($this->body)
                ->send();

            return true;
        }
        return false;
    }

}
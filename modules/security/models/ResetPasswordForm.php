<?php

namespace app\modules\security\models;

use Yii;
use app\modules\security\models\User;
use yii\base\Model;
use yii\base\InvalidParamException;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
    public $password, $password_repeat;

    /**
     * @var \app\modules\security\models\User
     */
    private $_user;


    /**
     * Creates a form model given a token.
     *
     * @param  string                          $token
     * @param  array                           $config name-value pairs that will be used to initialize the object properties
     * @throws \yii\base\InvalidParamException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException(\Yii::t('app', 'Token reset password can not be empty.'));
        }
        $this->_user = User::findByPasswordResetToken($token);
        if (!$this->_user) {
            throw new InvalidParamException(\Yii::t('app', 'Token reset password is invalid.'));
        }
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'password_repeat'], 'required'],
            [['password_repeat'], 'compare', 'compareAttribute' => 'password', 'message' => \Yii::t('app','New passwords do not match')],
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
            'password'          => \Yii::t('app', 'New Password'),
            'password_repeat'   => \Yii::t('app', 'New Password Confirm'),
        ];
    }

    /**
     * Resets password.
     *
     * @return boolean if password was reset.
     */
    public function resetPassword()
    {
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();

        return $user->save(false);
    }
}

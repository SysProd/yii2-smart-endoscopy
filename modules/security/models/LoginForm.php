<?php

namespace app\modules\security\models;

use Yii;

use yii\web\IdentityInterface;
use yii\base\Model;
use yii\base\InvalidValueException;
use app\modules\security\models\User;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username'      => \Yii::t('app', 'Login'),
            'password'      => \Yii::t('app', 'Password'),
            'rememberMe'    => \Yii::t('app', 'Remember me'),
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, \Yii::t('app', 'Incorrect username or password.'));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $model = $this->getUser(); // данные пользователя
            Yii::$app->session->close();
//            Yii::$app->db->createCommand()->delete(Yii::$app->session->id, ['user_id' => $model->id])->execute(); // Удаление всех сессий пользователя
            $model->generateAuthKey(); // Создать новый ключ авторизации
            if ($model->save(false,  ['auth_key'])){    // Пересохранение ключа авторизации пользователя для запрета нескольких сессий с одного акка
                return Yii::$app->user->login($model, $this->rememberMe ? 3600 : 0);
            }
            return false;
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }

}

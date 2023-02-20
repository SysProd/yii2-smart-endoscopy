<?php

namespace app\modules\security\models;


use Yii;

use yii\base\NotSupportedException;
use yii\web\IdentityInterface;
use yii\filters\RateLimitInterface;
use ethercreative\ratelimiter\IpRateLimitInterface;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\icons\Icon;
use igor162\adminlte\ColorCSS;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property boolean $send_mail
 * @property string $require_change
 * @property integer $confirmed_reg
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password
 * @property string $password_repeat
 * @property string $password_reset_token
 * @property integer $status_system
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_by
 * @property integer $updated_at
 * @property integer $ip
 * @property integer $rateLimit
 * @property integer $timePeriod
 * @property array $role
 * @property string $roleName
 * @property string $errorMessage
 * @property string $onlineByUser
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthAssignment[] $authAssignments0
 * @property AuthAssignment[] $authAssignments1
 * @property AuthItemChild[] $authItemChild
 * @property AuthItem[] $roleNameArray
 * @property AuthItem[] $roleNameString
 * @property AuthItem $rootRole
 * @property AuthItem[] $authItems
 * @property AuthItem[] $authItems0
 * @property AuthRule[] $authRules
 * @property AuthRule[] $authRules0
 *
 *
 * @property User $confirmedReg0
 * @property User $createdBy
 * @property User $updatedBy
 * @property User[] $usersList
 * @property User[] $users
 * @property User[] $users0
 * @property User[] $users1
 * @property UserProfile $userProfile
 * @property UserProfile[] $userProfiles
 * @property UserProfile[] $userProfiles0
 *
 * @property User[] $arrayListRole
 * @property User[] $statusSystemList
 * @property User $statusSystemStyle
 * @property User[] $onOffList
 * @property User $onOffStyle
 */

class User extends ActiveRecord implements IdentityInterface
{
    /** Вид формы при отправки */
    const   FORM_TYPE_AJAX = 'ajaxForm';

    /**
     * @var string IP пользователя системы
     */
    private $ip;

    /**
     * @var integer параметр ограничения кол-во запросов для пользователя
     */
    private $rateLimit;

    /**
     * @var integer параметр времени ограничения в секундах
     */
    private $timePeriod;

    /**
     * Состояние пользователя в системе
     */
    const   STATUS_OFFLINE        = 'offline',
            STATUS_ONLINE         = 'online',
            INTERVAL_STATUS       = 10;  // Интервал проверки стратуса пользователя

    /**
     * Статус в системе
     */
    const   STATUS_SYSTEM_ACTUAL        = 'Actual',     // 'Актуальный'
            STATUS_SYSTEM_IRRELEVANT    = 'Irrelevant', // 'Неактуальный'
            STATUS_SYSTEM_BLOCKED       = 'Blocked',    // 'Заблокированный'
            STATUS_SYSTEM_DELETED       = 'Deleted';    // 'Удаленный'

    /**
     * Сценарии использования модуля "Пользователя"
     */
    const   SCENARIO_UPDATE_USER  = 'update',
            SCENARIO_RESET_PASS   = 'reset',
            SCENARIO_ADD_USER     = 'create',
            SCENARIO_SEARCH_USER  = 'search';

    const   YES = 'Yes',
            NO  = 'No';

    public  $password,          // Пароль
            $password_repeat,   // Повторный ввод пароля
            $role = [],         // Роли пользователя в системе в массиве
            $roleName,          // Роли пользователя в системе в строке
            $send_mail = false, // отправка email
            $onlineByUser;

    /**
     * @var string блок ошибок при выполнении модели пользователя
     */
    private $errorMessage = '';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * Действие при Создании/Изменении в базе
     * @inheritdoc
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' =>
                    [
                        ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                        ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                    ],
//                'value' => time(),  // Атрибут типа даты
            ],
            [
                'class' => BlameableBehavior::className(),
                'attributes' =>
                    [
                        ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                        ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_by'],
                    ],
                'value' => empty( \Yii::$app->user->identity->id) ? NULL : \Yii::$app->user->identity->id,   // Изменить атрибут присвоения id
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'email', 'status_system' ], 'required', 'on' => self::SCENARIO_UPDATE_USER],
            [['username', 'email', 'status_system', 'password', 'password_repeat', ], 'required', 'on' => self::SCENARIO_ADD_USER],

            [['id', 'confirmed_reg', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['send_mail'], 'boolean'],

//            [['group_by',], 'filter', 'filter' => 'intval'], // фильтровать данные в числовой тип данных для правильной работы behaviors()

            [['username', 'email', 'password', 'password_repeat', 'auth_key', 'password_hash', 'password_reset_token'], 'trim'], // обрезать пробелы

            [['password_repeat'], 'compare', 'compareAttribute' => 'password', 'message' => \Yii::t('app','New passwords do not match')],   // проверка на введенные пароли

            [['send_mail'],      'default', 'value' => false ],
//            [['require_change'], 'default', 'value' => self::NO ],
            [['status_system'],  'default', 'value' => self::STATUS_SYSTEM_ACTUAL ],

            [['status_system'],  'in', 'range' => array_keys($this->statusSystemList) ],
            [['onlineByUser'],   'in', 'range' => array_keys($this->onOffList) ],

            [['email'],     'email'],
            [['email'],     'unique', 'targetClass' => User::className(),                                           'message' => \Yii::t('app', 'This «{attribute}» is already in use', ['attribute' => \Yii::t('app', 'E-mail')] )],  // проверка на уникальные «E-mail» в системе
            [['username'],  'unique', 'targetClass' => User::className(),                                           'message' => \Yii::t('app', 'This «{attribute}» is already in use', ['attribute' => \Yii::t('app', 'Username')] )],// проверка на уникальные «Пользователя» в системе
            [['username', 'email'], 'unique', 'targetAttribute' => ['username', 'email'],   'message' => \Yii::t('app', 'This «{attribute}» has been added',    ['attribute' => \Yii::t('app', 'User')] )],    // проверка на уникальные элементы

            [['username', 'email', 'password_hash', 'password_reset_token'],    'string', 'max' => 100],
            [['auth_key'],                                                      'string', 'max' => 32],
            [['username'],                      'string',  'min' => 3, 'max' => 100],
            [['password', 'password_repeat'],   'string',  'min' => 6, 'max' => 100],

            [['role'],                          'each', 'rule' => ['string']],

//            ['role', 'each', 'rule' => ['string']],
//            [['role',], 'required', 'message' => 'Необходимо выбрать "Роль в системе"'],
//            [['group_by'], 'unique'],

            [['confirmed_reg'],     'exist', 'skipOnError' => true, 'targetClass' => User::className(),        'targetAttribute' => ['confirmed_reg'   => 'id']],
            [['created_by'],        'exist', 'skipOnError' => true, 'targetClass' => User::className(),        'targetAttribute' => ['created_by'      => 'id']],
            [['updated_by'],        'exist', 'skipOnError' => true, 'targetClass' => User::className(),        'targetAttribute' => ['updated_by'      => 'id']],

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                    => Yii::t('app', 'ID - User'),
            'username'              => Yii::t('app', 'Login'),
            'email'                 => Yii::t('app', 'E-mail'),
            'confirmed_reg'         => Yii::t('app', 'Confirmed By'),
            'auth_key'              => Yii::t('app', 'Auth Key'),
            'password'              => Yii::t('app', 'Password'),
            'password_repeat'       => Yii::t('app', 'Confirm password'),
            'require_change'        => Yii::t('app', 'Require a change of password in the next sign in'),
            'password_hash'         => Yii::t('app', 'Hash password'),
            'password_reset_token'  => Yii::t('app', 'Token reset password'),
            'role'                  => Yii::t('app', 'Role in system'),
            'roleName'              => Yii::t('app', 'Role in system'),
            'rootRole'              => Yii::t('app', 'Root role'),
            'status_system'         => Yii::t('app', 'Status'),
            'created_at'            => Yii::t('app', 'Created At'),
            'created_by'            => Yii::t('app', 'Created By'),
            'updated_at'            => Yii::t('app', 'Updated At'),
            'updated_by'            => Yii::t('app', 'Updated By'),
            'send_mail'             => Yii::t('app', 'Notification to email'),
            'onlineByUser'          => Yii::t('app', 'Shape'),
        ];
    }

//    /**
//     * @param bool $insert
//     * @return bool
//     */
//    public function beforeSave($insert)
//    {
//        if (parent::beforeSave($insert)) {
//            if ($this->isNewRecord) {
//                $this->auth_key = \Yii::$app->security->generateRandomString();
//            }
//            return true;
//        }
//        return false;
//    }

    /**
     * Возвращает static данные пользователя
     * @param string $ip
     * @param int $rateLimit
     * @param int $timePeriod
     * @return static
     */
    public static function findByIp($ip, $rateLimit, $timePeriod)
    {
        $user = new static();

        $user->ip = $ip;
        $user->rateLimit = $rateLimit;
        $user->timePeriod = $timePeriod;

        return $user;
    }

    /**
     * Возвращает максимальное количество разрешенных запросов и размер окна времени.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @return array an array of two elements. The first element is the maximum number of allowed requests,
     * and the second element is the size of the window in seconds.
     */
    public function getRateLimit($request, $action)
    {
        return [$this->rateLimit, $this->timePeriod];
    }

    /**
     * Загружает количество разрешенных запросов и соответствующую временную метку из Cache.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @return array an array of two elements. The first element is the number of allowed requests,
     * and the second element is the corresponding UNIX timestamp.
     */
    public function loadAllowance($request, $action)
    {
        $cache = Yii::$app->getCache();

        return [
            $cache->get('user.ratelimit.ip.allowance.' . $this->ip),
            $cache->get('user.ratelimit.ip.allowance_updated_at.' . $this->ip),
        ];
    }

    /**
     * Сохраняет количество разрешенных запросов и соответствующую временную метку в Cache.
     * @param \yii\web\Request $request the current request
     * @param \yii\base\Action $action the action to be executed
     * @param integer $allowance the number of allowed requests remaining.
     * @param integer $timestamp the current timestamp.
     */
    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $cache = Yii::$app->getCache();

        $cache->set('user.ratelimit.ip.allowance.' . $this->ip, $allowance);
        $cache->set('user.ratelimit.ip.allowance_updated_at.' . $this->ip, $timestamp);
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean whether the email was send
     */
    public function sendEmail()
    {
        if ($this->email) {
            return Yii::$app
                ->mailer
                ->compose(
                    ['html' => 'new-users-in-system-html'],
                    ['model' => $this]
                )
                ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' robot'])
                ->setTo($this->email)
                ->setSubject(\Yii::t('app', 'Registration in the system'))
                ->send();
        }

        return false;
    }

    /**
     * Функция добаления Ролей для пользователя
     * @return bool
     */
    public function createRoles()
    {

        if(!empty($this->role) && !empty($this->id)){

            $transaction = \Yii::$app->db->beginTransaction();

            try {
                $flag = null;

                foreach ($this->role as $value) {
                    $roles = New AuthAssignment();
                    $roles->user_id = $this->id;
                    $roles->item_name = $value;

                    if(!($flag = $roles->save()) or !($roles->validate())){
                        $transaction->rollBack();
                        throw new \Exception(\Yii::t('app', 'Unexpected error'));   // Присвоение ошибки
                    }
                }
                if($flag){
                    $transaction->commit();
                    return true;
                }

            } catch (\Exception $ex) {
                print_r($ex->getMessage());
                $this->errorMessage .= \Yii::t('app', 'Failed to save «{attribute}»', ['attribute' => \Yii::t('app', 'role of user')]).': '.$ex->getMessage().'<br />';
                $transaction->rollBack();
                return false;
            }
        }

        return false;
    }

    /**
     * Функция обновления Ролей для пользователя
     * @return bool
     */
    public function updateRoles()
    {

//       Выбрать Роли из базы для выбраного пользователя
        $authAssignments = $this->authAssignments;

        /**
         * Удалить все отмеченые роли пользователя
         **/
        if (empty($this->role)) {
            if(AuthAssignment::deleteAll(['user_id' => $this->id])){
                return true;
            }
            $this->errorMessage .= \Yii::t('app', 'Failed to remove «{attribute}»', ['attribute' => \Yii::t('app', 'role of user')]).'<br />';
            return false;
        }

        /**
        * Удаление из массива страх элементов
        * Удаление из базы элементов отмеченых для удаления пользователем
        **/
        foreach ($authAssignments as $value) {
            $key = array_search($value->item_name, $this->role);
            if ($key === false) {
                echo $value->item_name;
                AuthAssignment::deleteAll(['item_name' => $value->item_name, 'user_id' => $this->id]);
            } else {
                unset($this->role[$key]);
            }
        }

        /**
         * Добавление новых отмеченых элементов пользователем в базу
         * Вывод ошибки при неудачном сохранении
         **/
        foreach ($this->role as $value) {
            try {

                $roles = New AuthAssignment();

                $roles->user_id = $this->id;
                $roles->item_name = $value;
                $roles->created_at = time();

                $roles->save(false);

            } catch (\Exception $ex) {
                $this->errorMessage .= \Yii::t('app', 'Failed to save «{attribute}»', ['attribute' => \Yii::t('app', 'role of user')]).': '.$ex->getMessage().'<br />';
            }
        }

    }

    /**
     * Список ошибок
     * @return string|null errorMessage
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status_system' => self::STATUS_SYSTEM_ACTUAL]);
    }

    /**
     * @inheritdoc
     * @param mixed $token
     * @param null $type
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException(\Yii::t('app', '«{attribute}» is not implemented.', ['attribute' => 'findIdentityByAccessToken']));
    }

    /**
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @return string current user auth key
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * @param string $authKey
     * @return bool if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
//        echo '<script>alert("' . __METHOD__ . '");</script>';
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Код автоматической генерации пароля
     * @param int $length
     * @return string
     */
    public function generatePassword ($length = 10)
    {
        $chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP!$%^&*()_^@#~?+=-.<>{}[],";
        $length = intval($length);
        $size=strlen($chars)-1;
        $password = "";
        while($length--) $password.=$chars[rand(0,$size)];
        return $password;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status_system' => self::STATUS_SYSTEM_ACTUAL]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status_system' => self::STATUS_SYSTEM_ACTUAL,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments0()
    {
        return $this->hasMany(AuthAssignment::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments1()
    {
        return $this->hasMany(AuthAssignment::className(), ['updated_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItems()
    {
        return $this->hasMany(AuthItem::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItems0()
    {
        return $this->hasMany(AuthItem::className(), ['updated_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthRules()
    {
        return $this->hasMany(AuthRule::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthRules0()
    {
        return $this->hasMany(AuthRule::className(), ['updated_by' => 'id']);
    }

    /**
     * Вывод "Ролей" пользователя в системе
     * @return \yii\db\ActiveQuery
     */
    public function getRoleNameArray()
    {
        return $this->hasMany( AuthItem::className(),       ['name' => 'item_name'])
                    ->viaTable(AuthAssignment::tableName(), ['user_id' => 'id']);
    }

    /**
     * Вывод "Ролей" пользователя в системе
     * @param null $urlActive
     * @return null|string
     */
    public function getRoleNameString($urlActive = null)
    {
        if(!empty($this->roleNameArray)){

            $html = [];

            $roleArray = $this->roleNameArray;
            $color = true;
            foreach ($roleArray as $id => $val){
                $style = $color ? ColorCSS::BG_GREEN : ColorCSS::BG_PURPLE; // цвет позиции
//                $html[] = Html::tag('span', '#'. $val->name, $style);
                if(!isset($urlActive)){
                    $html[] = Html::a('#' . $val->name, ['auth-item/update', 'id' => $val->name], ['class'=> 'label'. ' ' . $style]);
                }else{
                    $html[] = $val->name;
                }
                $color = !$color;
            }

            return implode(" ", $html) ;
        }
        else{
            return null;
        }
    }

    /**
     * Вывод "Задач" и "Операций" пользователя в системе
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChild()
    {
        return $this->hasMany(AuthItemChild::className(), ['parent' => 'name'])
            ->via('roleNameArray');   // вызов функции
    }

    /**
     * Проверка назначения роли "Администраторы" для пользователя
     * Проверка связи роли "Администраторы" с другими ролями в системе
     * @return true|false
     */
    public function getRootRole()
    {
        if (isset($this->roleNameArray) && isset($this->authItemChild)) {
            // Проверка на наличие роли "Root-Admin" у пользователя
            if (in_array(AuthItem::ROLE_Admin, ArrayHelper::map($this->roleNameArray, 'name', 'name'))) return true;
            // Проверка связанной роли "Root-Admin" у пользователя
            if (in_array(AuthItem::ROLE_Admin, ArrayHelper::map($this->authItemChild, 'parent', 'child'))) return true;
        }

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfirmedReg0()
    {
        return $this->hasOne(User::className(), ['id' => 'confirmed_reg']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    protected function getUsersList()
    {
        return ArrayHelper::map($this::find()->all(),'id','username');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['confirmed_reg' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers0()
    {
        return $this->hasMany(User::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers1()
    {
        return $this->hasMany(User::className(), ['updated_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfile()
    {
        return $this->hasOne(UserProfile::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfiles()
    {
        return $this->hasMany(UserProfile::className(), ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfiles0()
    {
        return $this->hasMany(UserProfile::className(), ['updated_by' => 'id']);
    }

    /**
     * Список ролей в системе выводимых для пользователя
     * @return array $items
     */
    public function getArrayListRole()
    {
        $date = AuthItem::find()
            ->where(['type' => AuthItem::TYPE_ROLE])
            ->all();

        // Запретить показывать роль "Администраторы", если "Пользователь" не является таковым
        if( !Yii::$app->user->can(AuthItem::ROLE_Admin) ) {
            $date = AuthItem::find()->where("name != '".AuthItem::ROLE_Admin."'")->andWhere(['type' => AuthItem::TYPE_ROLE])->all();
        }

        $items = ArrayHelper::map(
            $date,
            'name',
            function ($date) {
                return $date->name.(strlen($date->description) > 0 ? ' ['.$date->description.']' : '');
            }
        );

        return $items;
    }

    /**
     * Статус пользователя в системе
     * @return array
     */
    public function getStatusSystemList()
    {
        $array = [
            self::STATUS_SYSTEM_ACTUAL      => Yii::t('app', self::STATUS_SYSTEM_ACTUAL),
            self::STATUS_SYSTEM_IRRELEVANT  => Yii::t('app', self::STATUS_SYSTEM_IRRELEVANT),
            self::STATUS_SYSTEM_BLOCKED     => Yii::t('app', self::STATUS_SYSTEM_BLOCKED),
            self::STATUS_SYSTEM_DELETED     => Yii::t('app', self::STATUS_SYSTEM_DELETED),
                    ];

        // Скрыть статус "Удаленные"
        if ( !(Yii::$app->user->can(AuthItem::OPR_DeleteUser)) )      { ArrayHelper::remove($array, self::STATUS_SYSTEM_DELETED); }
//        // Скрыть статус "Заблокированные"
//        if ( !(Yii::$app->user->can('users-show-status_blocked')) )     { ArrayHelper::remove($array, self::STATUS_SYSTEM_BLOCKED); }
//        // Скрыть статус "Неактуальный"
//        if ( !(Yii::$app->user->can('users-show-status_irrelevant')) )  { ArrayHelper::remove($array, self::STATUS_SYSTEM_IRRELEVANT); }

        return $array;
    }

    /**
     * Функция вывода стилизованного "status_system"
     * @return string
     */
    public function  getStatusSystemStyle()
    {
              if( $this->status_system == self::STATUS_SYSTEM_ACTUAL )      {
            return '<span class="label label-success"   title="'. Yii::t('app', $this->status_system).'">'. Icon::show('user',          ['class' => 'fa-lg']) .'</span>';
        }else if( $this->status_system == self::STATUS_SYSTEM_IRRELEVANT )  {
            return '<span class="label label-warning"   title="'. Yii::t('app', $this->status_system).'">'. Icon::show('low-vision',    ['class' => 'fa-lg']) .'</span>';
        }else if( $this->status_system == self::STATUS_SYSTEM_BLOCKED )     {
            return '<span class="label label-fired"     title="'. Yii::t('app', $this->status_system).'">'. Icon::show('ban',           ['class' => 'fa-lg']) .'</span>';
        }else if( $this->status_system == self::STATUS_SYSTEM_DELETED )     {
            return '<span class="label label-danger"    title="'. Yii::t('app', $this->status_system).'">'. Icon::show('user-times',    ['class' => 'fa-lg']) .'</span>';
        }else{
            return '---';
        }
    }

    /**
     * Массив с выражением
     * @return array
     */
    public static function getYesNoList()
    {
        return
            [
                self::YES   => Yii::t('app', self::YES),
                self::NO    => Yii::t('app', self::NO),
            ];
    }

    /**
     * Стиль выражения исходя от его значения
     * @param $type
     * @return mixed
     */
    public static function  getYesNoStyle($type)
    {

        $style = [
            self::YES   => '<span class="label label-success"   title="'  . Yii::t('app', self::YES).'">' . Icon::show('check', ['class' => 'fa-lg']). '</span>',
            self::NO   => '<span class="label label-danger"     title="'  . Yii::t('app', self::NO) .'">' . Icon::show('remove', ['class' => 'fa-lg']) . '</span>',
        ];

        return ArrayHelper::getValue($style, $type);
    }

    /**
     * Массив с выражением
     * @return array
     */
    public function getOnOffList()
    {
        return
            [
                self::STATUS_OFFLINE   => Yii::t('app', self::STATUS_OFFLINE),
                self::STATUS_ONLINE    => Yii::t('app', self::STATUS_ONLINE),
            ];
    }

}

<?php

namespace app\modules\staff\models;

use Yii;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

use yii\db\ActiveRecord;

use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

use kartik\icons\Icon;

use app\modules\security\models\AuthItem;
use app\modules\staff\models\Phone;
use app\modules\security\models\User;

/**
 * This is the model class for table "staff".
 *
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property string $patronymic
 * @property string $gender
 * @property string $email
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_by
 * @property integer $updated_at
 *
 * @property Phone $phoneDefault
 * @property Phone[] $phonesForUser
 * @property Staff $fullName
 * @property Staff $shortName
 * @property Staff $checkRegStaff
 * @property User $user
 * @property Staff $createdBy
 * @property Staff $updatedBy
 * @property Staff $gender0
 * @property Staff[] $genderList
 */
class Staff extends \yii\db\ActiveRecord
{
    /** Вид формы при отправки */
    const   FORM_TYPE_AJAX = 'ajaxForm';

    /**
     * Гендорная принадлежность
     */
    const   GENDER_MALE     = 'Male',       // Мужской
            GENDER_FEMALE   = 'Female';     // Женский

    /**
     * Сценарии использования модуля Сотрудники
     */
    const   SCENARIO_UPDATE_STATUS  = 'update_status',
            SCENARIO_ADD_STAFF      = 'create_staff';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users_staff';
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
                'value' => Yii::$app->user->identity->profile_id,   // Изменить атрибут присвоения profile_id
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['first_name', 'last_name', 'patronymic', 'gender',], 'required', 'on' => self::SCENARIO_ADD_STAFF],
            [['status_system'],   'required', 'on' => self::SCENARIO_UPDATE_STATUS],

            [['id',  'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
//            [['company_id', 'counterparty_id', 'function_id', 'department_id',], 'filter', 'filter' => 'intval'], // фильтровать данные в числовой тип данных

            [['first_name', 'last_name', 'patronymic','email'], 'trim'], // обрезать пробелы

            [['first_name', 'last_name', 'patronymic',],  'unique', 'targetClass' => $this::className(), 'message' => \Yii::t('app', 'This «{attribute}» has been added', ['attribute' => \Yii::t('app', 'Employee')] )],

            [['first_name', 'last_name', 'patronymic', 'email'], 'string', 'max' => 45],
            [['fullName'],  'string', 'max' => 100],
            [['shortName'], 'string', 'max' => 100],

            [['gender'],           'in',        'range' => array_keys($this->genderList)],

            [['email'], 'email'],
            [['email'],     'unique', 'targetClass' => Staff::className(), 'message' => \Yii::t('app', 'This «{attribute}» is already in use', ['attribute' => \Yii::t('app', 'E-mail')] )],  // проверка на уникальные «E-mail» в системе

            [['created_by'],        'exist', 'skipOnError' => true, 'targetClass' => Staff::className(),        'targetAttribute' => ['created_by'      => 'id']],
            [['updated_by'],        'exist', 'skipOnError' => true, 'targetClass' => Staff::className(),        'targetAttribute' => ['updated_by'      => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'        => Yii::t('app', 'ID - Profile'),
            'first_name'        => Yii::t('app', 'First Name'),
            'last_name'         => Yii::t('app', 'Last Name'),
            'patronymic'        => Yii::t('app', 'Patronymic'),
            'fullName'          => Yii::t('app', 'Full name'),
            'shortName'         => Yii::t('app', 'Short name'),
            'gender'            => Yii::t('app', 'Gender'),
            'email'             => Yii::t('app', 'E-mail'),
            'phoneDefault'      => Yii::t('app', 'Phone'),
            'created_at'        => Yii::t('app', 'Created At'),
            'created_by'        => Yii::t('app', 'Created By'),
            'updated_at'        => Yii::t('app', 'Updated At'),
            'updated_by'        => Yii::t('app', 'Updated By'),
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public static function findByAll()
    {
        $query = static::find()
//            ->joinWith(['phoneDefault'])    // Подключение телефонного справочника
//            ->leftJoin( Phone::tableName() . ' as `phoneDef` on `phoneDef` .`user_id` = '.self::tableName().'.`id`'  ) // Подключение телефонного справочника

            ->leftJoin( self::tableName().' as `create`    on `create`   .`id`    = '.self::tableName().'.`created_by`')       // Создал
            ->leftJoin( self::tableName().' as `update`    on `update`   .`id`    = '.self::tableName().'.`updated_by`');      // Обновил

        // * Показывать сотрудников, доступных для роли пользователя
        // Заблокированные сотрудники
/*        if( Yii::$app->user->can('staff-show-status_blocked') ){
            $query->orWhere([Staff::tableName().'.status_system'=>Staff::STATUS_SYSTEM_BLOCKED]);
        }
        // Уволенные сотрудники
        if( Yii::$app->user->can('staff-show-status_dismissed') ){
            $query->orWhere([Staff::tableName().'.status_system'=>Staff::STATUS_SYSTEM_DISMISSED]);
        }
        // Удаленные сотрудники
        if( Yii::$app->user->can('staff-show-status_delete') ){
            $query->orWhere([Staff::tableName().'.status_system'=>Staff::STATUS_SYSTEM_DELETED]);
        }*/

        return $query;

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhoneDefault()
    {
        return $this->hasOne(Phone::className(), ['user_id' => 'id' ])
            ->where(['type_phone' => Phone::TYPE_MOBILE, 'status_phone' => Phone::STATUS_AVAILABLE, 'default_phone' => Phone::PHONE_DEFAULT, ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhonesForUser()
    {
        return $this->hasMany(Phone::className(), ['user_id' => 'id' ]);
    }

    /**
     * Функция вывода полного имени пользователя
     * @return string
     */
    public function getFullName()
    {
        return empty($this->last_name) && empty($this->first_name) && empty($this->patronymic) ? null : Html::encode($this->last_name.' '.$this->first_name.' '.$this->patronymic);
    }

    /**
     * Функция вывода ФИО пользователя
     * @return string
     */
    public function getShortName()
    {
        return empty($this->last_name) && empty($this->first_name) && empty($this->patronymic) ? null : Html::encode($this->last_name.' '.mb_substr($this->first_name,0,1,'UTF-8').'. '.mb_substr($this->patronymic,0,1,'UTF-8').'.');
    }

    /**
     * Функция проверки зарегистрирован ли сотрудник в системе согласно профилю
     * @param null $id
     * @return User|bool|string
     */
    public static function checkRegStaff($id = null)
    {
        /**
         * @var $date \app\modules\security\models\User
         */
        if($date = User::find()->where(['id' => $id])->one()){
            //  Запретить вывод данных пользователя со статусом "Удаленный" и с ролью AuthItem::ROLE_ExecAdmin
            if( !Yii::$app->user->can(AuthItem::ROLE_Admin) ){  if($date->rootRole or $date->status_system === User::STATUS_SYSTEM_DELETED){ return false; } }
            return true;
        }
            return false;
//        return $this->last_name.' '.$this->first_name.' '.$this->patronymic;
    }

     /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['profile_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(Staff::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(Staff::className(), ['id' => 'updated_by']);
    }

    /**
     * Список гендорной принадлежности
     * @return array
     */
    public function getGenderList()
    {
        return [
            self::GENDER_MALE   => Yii::t('app', self::GENDER_MALE),
            self::GENDER_FEMALE => Yii::t('app', self::GENDER_FEMALE),
        ];
    }

    /**
     * Найти гендорную принадлежность из массива
     * @return Staff|string
     */
    public function getGender0()
    {
        $data = $this->genderList;
        return isset($data[$this->gender]) ? $data[$this->gender] : '---';
    }

}

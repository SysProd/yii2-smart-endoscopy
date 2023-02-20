<?php

namespace app\modules\security\models;

use Yii;

use yii\helpers\Html;


/**
 * This is the model class for table "user_profile".
 *
 * @property integer $id
 * @property string $first_name
 * @property string $last_name
 * @property string $patronymic
 * @property string $gender
 * @property integer $avatar_img
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_by
 * @property integer $updated_at
 *
 * @property UserProfile $fullName
 * @property UserProfile $shortName
 * @property StorageFile $avatarImg
 * @property User $idUser
 * @property User $createdBy
 * @property User $updatedBy
 * @property UserProfile $gender0
 * @property UserProfile[] $genderList
 */
class UserProfile extends \yii\db\ActiveRecord
{

    /**
     * Гендорная принадлежность
     */
    const   GENDER_MALE     = 'Male',       // Мужской
            GENDER_FEMALE   = 'Female';     // Женский

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users_staff';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id',/* 'first_name',*/ 'gender'], 'required'],
            [['id', /*'avatar_img',*/ 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['gender'], 'string'],
            [['first_name', 'last_name', 'patronymic'], 'string', 'max' => 45],

            [['gender'],           'in',        'range' => array_keys($this->genderList)],

            [['id'], 'unique'],

            [['first_name', 'last_name', 'patronymic',],  'unique', 'targetClass' => $this::className(), 'message' => \Yii::t('app', 'This «{attribute}» has been added', ['attribute' => \Yii::t('app', 'User')] )],


//            [['avatar_img'], 'exist', 'skipOnError' => true, 'targetClass' => StorageFile::className(), 'targetAttribute' => ['avatar_img' => 'id']],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Id Profile'),
            'last_name' => Yii::t('app', 'Last Name'),
            'first_name' => Yii::t('app', 'First Name'),
            'patronymic' => Yii::t('app', 'Patronymic'),
            'gender' => Yii::t('app', 'Gender'),
            'avatar_img' => Yii::t('app', 'Avatar'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAvatarImg()
    {
        return $this->hasOne(StorageFile::className(), ['id' => 'avatar_img']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdUser()
    {
        return $this->hasOne(User::className(), ['id' => 'id']);
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
     * Функция вывода полного ФИО пользователя
     * @return string
     */
    public function getFullName()
    {

        $fullName = array();

        $fullName[] = Html::encode($this->last_name);
        $fullName[] = Html::encode($this->first_name);
        $fullName[] = Html::encode($this->patronymic);

        $fullName = !empty($fullName = trim(implode(" ", $fullName))) ? $fullName : null;

        return $fullName;
    }

    /**
     * Функция вывода сокращенного ФИО пользователя
     * @return string
     */
    public function getShortName()
    {
        $shortName = array();

        $shortName[] = !empty($this->last_name) ? Html::encode($this->last_name) : null;
        $shortName[] = !empty($this->first_name) ? Html::encode(mb_substr($this->first_name,0,1,'UTF-8')) : null;
        $shortName[] = !empty($this->patronymic) ? Html::encode(mb_substr($this->patronymic,0,1,'UTF-8')) : null;

        $shortName = !empty($shortName = trim(implode(" ", $shortName))) ? $shortName : null;

        return $shortName;
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
     * @return UserProfile|string
     */
    public function getGender0()
    {
        $data = $this->genderList;
        return isset($data[$this->gender]) ? $data[$this->gender] : '---';
    }

    /**
     * Добавление сотового номера телефона
     * @return array|null $items
     */
    /*    public function createPhone()
        {
            $phone = New Phone();

            $phone_intenget = $this->phone;
            $phone_string = mb_substr(Yii::$app->formatter->asPhoneFormatter($this->phone),1);

            $phone->user_id = $this->id;
            $phone->type_phone = Phone::TYPE_MOBILE;
            $phone->status_phone = Phone::STATUS_AVAILABLE;
            $phone->default_phone = Phone::PHONE_DEFAULT;
            $phone->phone_reference = $phone_intenget;
            $phone->phone_template = $phone_string;
            $phone->created_at = time();
            $phone->add_user = Yii::$app->user->identity->id;
            print_r($phone);

            try {
                $phone->save();
            } catch (\Exception $ex) {
                $this->errorMessage .= Yii::t('app', "Item <strong>{value}</strong> is not assigned:", ['value' => $phone_intenget]). " " . $ex->getMessage() . "<br />";

    //            $this->errorMessage .= Yii::$app->session->addFlash('error', '<h4><span class="glyphicon glyphicon-ok-sign kv-alert-title"></span> Ошибка</h4>  <p> Телефон № <strong> '.$phone_intenget.' </strong> не сохранен. '). " " . $ex->getMessage() . "<br />";
            }
    //        return $phone->id_phone;

        }*/
}

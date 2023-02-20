<?php

namespace app\modules\security\models;

use Yii;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "auth_assignment".
 *
 * @property string $item_name
 * @property integer $user_id
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_by
 * @property integer $updated_at
 *
 * @property AuthItem $itemName
 * @property User $user
 * @property User $createdBy
 * @property User $updatedBy
 */
class AuthAssignment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_assignment';
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
//                        ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                    ],
//                'value' => time(),  // Атрибут типа даты
            ],
            [
                'class' => BlameableBehavior::className(),
                'attributes' =>
                    [
                        ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
//                        ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_by'],
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
            [['item_name', 'user_id'], 'required'],
            [['user_id', 'created_at', 'created_by'/*, 'updated_at', 'updated_by'*/], 'integer'],
            [['item_name'], 'string', 'max' => 64],

            [['user_id',], 'filter', 'filter' => 'intval'], // фильтровать данные в числовой тип данных для правильной работы behaviors()
            [['item_name', 'user_id'], 'unique', 'targetAttribute' => ['item_name', 'user_id'],   'message' => \Yii::t('app', 'This «{attribute}» has been added',    ['attribute' => \Yii::t('app', 'Role')] )],    // проверка на уникальные элементы
//            [['item_name'], 'unique'],

            [['item_name'],     'exist', 'skipOnError' => true, 'targetClass' => AuthItem::className(),     'targetAttribute' => ['item_name'   => 'name']],
            [['user_id'],       'exist', 'skipOnError' => true, 'targetClass' => User::className(),         'targetAttribute' => ['user_id'     => 'id']],
            [['created_by'],    'exist', 'skipOnError' => true, 'targetClass' => User::className(),        'targetAttribute' => ['created_by'  => 'id']],
//            [['updated_by'],    'exist', 'skipOnError' => true, 'targetClass' => User::className(),        'targetAttribute' => ['updated_by'  => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'item_name'     => \Yii::t('app', 'Name'),      // 'Название роли в системе'
            'user_id'       => \Yii::t('app', 'User'),      // 'ID - пользователя для которой пренадлежит эта роль'
            'created_at'    => \Yii::t('app', 'Created At'),
            'created_by'    => \Yii::t('app', 'Created By'),
//            'updated_at'    => \Yii::t('app', 'Updated At'),
//            'updated_by'    => \Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItemName()
    {
        return $this->hasOne(AuthItem::className(), ['name' => 'item_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
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

}

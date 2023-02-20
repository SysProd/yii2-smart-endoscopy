<?php

namespace app\modules\security\models;

use Yii;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\rbac\Rule;

/**
 * This is the model class for table "auth_rule".
 *
 * @property string $name
 * @property string $data
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_by
 * @property integer $updated_at
 *
 * @property AuthItem[] $authItems
 * @property User $createdBy
 * @property User $updatedBy
 */
class AuthRule extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_rule';
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
            [['name'], 'required'],
            [['data'], 'string'],
            [['name'], 'string', 'max' => 64],

            [['created_by'],    'exist', 'skipOnError' => true, 'targetClass' => User::className(),        'targetAttribute' => ['created_by'  => 'id']],
            [['updated_by'],    'exist', 'skipOnError' => true, 'targetClass' => User::className(),        'targetAttribute' => ['updated_by'  => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'          => \Yii::t('app', 'Name'),      // 'Название роли в системе'
            'data'          => \Yii::t('app', 'Serialize'), //'serialize - Генерируемое хранимое представление значения'
            'created_at'    => \Yii::t('app', 'Created At'),
            'created_by'    => \Yii::t('app', 'Created By'),
            'updated_at'    => \Yii::t('app', 'Updated At'),
            'updated_by'    => \Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItems()
    {
        return $this->hasMany(AuthItem::className(), ['rule_name' => 'name']);
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

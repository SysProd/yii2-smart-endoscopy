<?php

namespace app\modules\staff\models;

use Yii;

use yii\db\ActiveRecord;

use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

use kartik\icons\Icon;

use app\modules\staff\models\Staff;

/**
 * This is the model class for table "phone".
 *
 * @property integer $id
 * @property integer $id_phone
 * @property integer $user_id
 * @property string $type_phone
 * @property string $phone_reference
 * @property string $phone_template
 * @property string $status_phone
 * @property integer $default_phone
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_by
 * @property integer $updated_at
 * @property string $comment
 * @property boolean $count_default_phone
 *
 * @property Staff $staff
 * @property Staff $createdBy
 * @property Staff $updatedBy
 * @property Phone[] $typeList
 * @property Phone[] $statusList
 * @property Phone[] $statusSystemList
 * @property Phone[] $defaultPhoneList
 * @property Phone $typeStyle
 * @property Phone $statusStyle
 * @property Phone $statusSystemStyle
 * @property Phone $defaultPhoneStyle
 */
class Phone extends \yii\db\ActiveRecord
{

    const   TYPE_MOBILE     = 'Mobile',     // 'Сотовый'
            TYPE_LANDLINE   = 'Landline',   // 'Городской'
            TYPE_FAX        = 'Fax',        // 'Факс'
            TYPE_INTERNAL   = 'Internal';   // 'Внутрений'

    /**
     * Статус телефона
     */
    const   STATUS_AVAILABLE        = 'Available',      // 'Доступен'
            STATUS_UNAVAILABLE      = 'Unavailable';    // 'Недоступен'

    const PHONE_DEFAULT = '1';      // 'Основной' телефон

    public $id;
    public $count_default_phone = false;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users_phone';
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
                'value' => empty(\Yii::$app->user->identity->id) ? NULL : \Yii::$app->user->identity->id,   // Изменить атрибут присвоения id
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_phone', 'status_phone', 'phone_reference'], 'required'],

            [['id', 'id_phone', 'user_id', 'default_phone', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
//            [['user_id',], 'filter', 'filter' => 'intval'], // фильтровать данные в числовой тип данных

            [['phone_reference', 'phone_template', 'comment'], 'trim'], // обрезать пробелы

            [['phone_reference'],   'string', 'min' => 10, 'max' => 17 ],
            [['phone_template'],    'string', 'max' => 45 ],
            [['comment'],           'string', 'max' => 200 ],

            [['default_phone'], 'default',   'value' => false ],
            [['type_phone'],    'default',   'value' => self::TYPE_MOBILE ],
            [['status_phone'],  'default',   'value' => self::STATUS_AVAILABLE ],

            [['type_phone'],    'in',        'range' => array_keys($this->typeList)],
            [['status_phone'],  'in',        'range' => array_keys($this->statusList)],

            [['default_phone',], 'customValidate'],
            [['phone_reference'], 'unique', 'targetClass' => Phone::className(), 'message' => Yii::t('app', 'This «{attribute}» has been added', ['attribute' => \Yii::t('app', 'Phone')] )],

            [['user_id'],           'exist', 'skipOnError' => true, 'targetClass' => Staff::className(),        'targetAttribute' => ['user_id'         => 'id']],
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
            'id_phone'          => Yii::t('app', 'ID - Phone'),
            'user_id'           => Yii::t('app', 'Staff member'),
            'type_phone'        => Yii::t('app', 'Type of phone'),
            'status_phone'      => Yii::t('app', 'Status of phone'),
            'default_phone'     => Yii::t('app', 'Basic phone'),
            'phone_reference'   => Yii::t('app', 'Number of phone'),
            'phone_template'    => Yii::t('app', 'Template of phone'),
            'comment'           => Yii::t('app', 'Comment'),
            'created_by'        => Yii::t('app', 'Created By'),
            'updated_by'        => Yii::t('app', 'Updated By'),
            'created_at'        => Yii::t('app', 'Created At'),
            'updated_at'        => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function findByAll()
    {
        $query = static::find()
            ->leftJoin( Staff::tableName().' as `fixedStaff`   on `fixedStaff`.`id_profile` = '.self::tableName().'.`user_id`')
            ->leftJoin( Staff::tableName().' as `create`    on `create` .`id_profile`    = '.self::tableName().'.`created_by`')  // Создал
            ->leftJoin( Staff::tableName().' as `update`    on `update` .`id_profile`    = '.self::tableName().'.`updated_by`'); // Обновил

        return $query;

    }

    /**
     * ## Проверить ###
     * Вывод ошибки, если не отмечен общий номер телефона
     * @param $attribute
     * @param $params
     * @return $this
     */
    public function customValidate($attribute, $params)
    {
        if($this->count_default_phone){
            $this->addError('default_phone', '');
        }
        return $this;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaff()
    {
        return $this->hasOne(Staff::className(), ['id' => 'user_id']);
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
     * Тип телефона
     * @return array
     */
    public function getTypeList()
    {
        return [
            self::TYPE_MOBILE   =>  Yii::t('app', self::TYPE_MOBILE),
            self::TYPE_LANDLINE =>  Yii::t('app', self::TYPE_LANDLINE),
            self::TYPE_FAX      =>  Yii::t('app', self::TYPE_FAX),
        ];
    }

    /**
     * Функция вывода стилизованного "type_phone"
     */
    public function  getTypeStyle()
    {
        if($this->type_phone == Phone::TYPE_MOBILE){
            return '<span class="label label-success" title="'. Yii::t('app', $this->type_phone).  '">'. Icon::show('mobile',      ['class' => 'fa-lg']) . '</span>';
        }else if ($this->type_phone == Phone::TYPE_LANDLINE){
            return '<span class="label label-warning" title="'. Yii::t('app', $this->type_phone).  '">'. Icon::show('phone-square',['class' => 'fa-lg']) . '</span>';
        }else if ($this->type_phone == Phone::TYPE_FAX){
            return '<span class="label label-info" title="'.    Yii::t('app', $this->type_phone).  '">'. Icon::show('fax',         ['class' => 'fa-lg']) . '</span>';
        }else{
            return '---';
        }
    }

    /**
     * Статус телефона
     * @return array
     */
    public function getStatusList()
    {
        return [
            self::STATUS_AVAILABLE      =>  Yii::t('app', self::STATUS_AVAILABLE),
            self::STATUS_UNAVAILABLE    =>  Yii::t('app', self::STATUS_UNAVAILABLE),
        ];
    }

    /**
     * Функция вывода стилизованного "status_phone"
     */
    public function  getStatusStyle()
    {
        if($this->status_phone == Phone::STATUS_AVAILABLE){
            return '<span class="label label-success" title="'. Yii::t('app', $this->status_phone).'">'. Icon::show('volume-control-phone', ['class' => 'fa-lg']) . '</span>';
        }else if ($this->status_phone == Phone::STATUS_UNAVAILABLE){
            return '<span class="label label-warning" title="'. Yii::t('app', $this->status_phone).'">'. Icon::show('tty',                  ['class' => 'fa-lg']) . '</span>';
        }else{
            return '---';
        }
    }

    /**
     * Функция вывода массива "default_phone"
     */
    public function getDefaultPhoneList()
    {
        return
            [
                true   => Yii::t('app', 'Basic'),
                false  => Yii::t('app', 'General'),
            ];
    }

    /**
     * Функция вывода стилизованного "default_phone"
     */
    public function  getDefaultPhoneStyle()
    {
        if ($this->default_phone == true) {
            return '<span class="label label-success" title="'. Yii::t('app', 'Basic').  '">'. Icon::show('check',      ['class' => 'fa-lg']) . '</span>';
        } else {
            return '<span class="label label-danger"  title="'. Yii::t('app', 'General').'">'. Icon::show('share-alt',  ['class' => 'fa-lg']) . '</span>';
        }
    }
}

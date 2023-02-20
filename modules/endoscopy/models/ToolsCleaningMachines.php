<?php

namespace app\modules\endoscopy\models;

use Yii;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

use yii\caching\TagDependency;

use kartik\icons\Icon;

use app\modules\security\models\User;
use app\modules\staff\models\Staff;

/**
 * This is the model class for table "tools_cleaning_machines".
 *
 * @property int $id
 * @property string|null $name Наименовение машины
 * @property int|null $mode Режим работы
 * @property string|null $comment Комментарии
 * @property int|null $created_by Добавил
 * @property int|null $created_at Создан
 * @property int|null $updated_by Обновил
 * @property int|null $updated_at Обновлен
 *
 * @property CleaningLog[] $cleaningLogs
 * @property ToolsCleaningAgents $cleaningAgentsBy
 * @property Staff $createdBy
 * @property Staff $updatedBy
 * @property RfidTags[] $rfidTags
 * */
class ToolsCleaningMachines extends \yii\db\ActiveRecord
{
    /** Вид формы при отправки */
    const   FORM_TYPE_AJAX = 'ajaxForm';

    /**
     * Сценарии использования модуля Disguise
     */
    const   SCENARIO_UPDATE = 'update',
            SCENARIO_CREATE = 'create',
            SCENARIO_SEARCH = 'search';

    const   YES = 'Yes',
            NO  = 'No';

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
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tools_cleaning_machines';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],

            [['name'],  'unique', 'targetClass' => $this::className(), 'message' => \Yii::t('app', 'This «{attribute}» has been added', ['attribute' => \Yii::t('app', 'Cleaning Tool')] )],

            [['mode', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 45],
            [['comment'], 'string', 'max' => 255],

            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'mode' => Yii::t('app', 'Mode of operation'),
            'comment' => Yii::t('app', 'Comment'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function findByAll()
    {

        $query = static::find()

//            ->leftJoin(ToolsCleaningAgents::tableName() . ' as `cleaning_agents` on `cleaning_agents` .`id` = ' . self::tableName() . '.`cleaning_agents_by`') // Средства очистки
            ->leftJoin(User::tableName() . ' as `create` on `create` .`id` = ' . self::tableName() . '.`created_by`') // Создал
            ->leftJoin(User::tableName() . ' as `update` on `update` .`id` = ' . self::tableName() . '.`updated_by`'); // Обновил

        return $query;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCleaningLogs()
    {
        return $this->hasMany(CleaningLog::className(), ['disinfection_auto_by' => 'id']);
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
     * @return \yii\db\ActiveQuery
     */
    public function getRfidTags()
    {
        return $this->hasMany(RfidTags::className(), ['fx_tools_machines' => 'id']);
    }

}

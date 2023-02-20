<?php

namespace app\modules\endoscopy\models;

use igor162\adminlte\ColorCSS;
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
 * This is the model class for table "cleaning_log".
 *
 * @property int $id
 * @property int|null $add_data Дата добавления
 * @property int|null $tools_by ID - Эндоскопа
 * @property int|null $test_tightness_by Тест на герметичность, выбор статуса (Да,Нет)
 * @property int|null $cleaning_agents_by ID - Средство очистки
 * @property int|null $cleaning_start Время начала очистки
 * @property int|null $cleaning_end Время окончания очистки
 * @property int|null $test_qualities_cleaning_date Дата Теста на качество очистки
 * @property int|null $test_qualities_cleaning_status Статус Теста на качество очистки
 * @property string|null $disinfection_type_by Ручная очистка или автоматическая
 * @property int|null $disinfection_auto_by Выполнение Дезинфекции Авто
 * @property int|null $disinfection_auto_agents_by ID - Средство очистки
 * @property int|null $disinfection_auto_start Начало дезинфекции
 * @property int|null $disinfection_auto_end Конец дезинфекции
 * @property int|null $disinfection_manual_by Выполнение Дезинфекции ручной
 * @property int|null $disinfection_manual_start Начало дезинфекции
 * @property int|null $disinfection_manual_end Конец дезинфекции
 * @property int|null $cleaning_tools_start Начало очистки инструмента
 * @property int|null $cleaning_tools_end Завершение очистки инструмента
 * @property int $staff_by Сотрудник выполняющий операции
 * @property int|null $updated_at Обновлен
 * @property string $status_log Статус документа
 *
 * @property CleaningLevelLog $cleaningLevelLog
 * @property RfidTags $toolsBy
 * @property RfidTags $cleaningAgentsBy
 * @property ToolsCleaningAgents[] $cleaningAgentsList
 * @property RfidTags $testQualitiesCleaningStatus
 * @property Staff[] $staffList
 * @property Staff $staffBy
 * @property RfidTags $testTightnessBy
 * @property RfidTags $disinfectionAutoBy
 * @property RfidTags $disinfectionAutoAgentsBy
 * @property RfidTags $disinfectionManualBy
 * @property CleaningLog[] $disinfectionTypeList
 * @property CleaningLog $disinfectionTypeStyle
 * @property CleaningLog[] $statusLogList
 * @property CleaningLog $statusLogStyle
 */
class CleaningLog extends \yii\db\ActiveRecord
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

    const   manual = 'Manual',
            auto  = 'Auto';

    /**
     * Статус документа в системе
     */
    const   STATUS_ACTUAL  = 'Actual',     // 'Актуальный'
            STATUS_COMPLETED = 'Completed'; // 'Неактуальный'


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
                        ActiveRecord::EVENT_BEFORE_INSERT => ['add_data'],
                        ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                    ],
            ],
/*            [
                'class' => BlameableBehavior::className(),
                'attributes' =>
                    [
                        ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                        ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_by'],
                    ],
                'value' => empty(\Yii::$app->user->identity->id) ? NULL : \Yii::$app->user->identity->id,   // Изменить атрибут присвоения id
            ],*/
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cleaning_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['add_data', 'staff_by'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],

            [['add_data', 'tools_by', 'test_tightness_by', 'cleaning_agents_by', 'cleaning_start', 'cleaning_end', 'test_qualities_cleaning_date', 'test_qualities_cleaning_status', 'disinfection_auto_by', 'disinfection_auto_agents_by', 'disinfection_auto_start', 'disinfection_auto_end', 'disinfection_manual_by', 'disinfection_manual_start', 'disinfection_manual_end', 'cleaning_tools_start', 'cleaning_tools_end', 'staff_by', 'updated_at'], 'integer'],

            [['disinfection_type_by', 'status_log'], 'string'],
            [['disinfection_type_by'],  'in', 'range' => array_keys($this->disinfectionTypeList)],

            [['status_log'],  'in', 'range' => array_keys($this->statusLogList)],
            [['status_log'],  'default', 'value' => self::STATUS_ACTUAL ],

            [['staff_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['staff_by' => 'id']],

            [['tools_by'], 'exist', 'skipOnError' => true, 'targetClass' => RfidTags::className(), 'targetAttribute' => ['tools_by' => 'id']],
            [['test_qualities_cleaning_status'], 'exist', 'skipOnError' => true, 'targetClass' => RfidTags::className(), 'targetAttribute' => ['test_qualities_cleaning_status' => 'id']],
            [['cleaning_agents_by'], 'exist', 'skipOnError' => true, 'targetClass' => RfidTags::className(), 'targetAttribute' => ['cleaning_agents_by' => 'id']],
            [['test_tightness_by'], 'exist', 'skipOnError' => true, 'targetClass' => RfidTags::className(), 'targetAttribute' => ['test_tightness_by' => 'id']],
            [['disinfection_auto_by'], 'exist', 'skipOnError' => true, 'targetClass' => RfidTags::className(), 'targetAttribute' => ['disinfection_auto_by' => 'id']],
            [['disinfection_auto_agents_by'], 'exist', 'skipOnError' => true, 'targetClass' => RfidTags::className(), 'targetAttribute' => ['disinfection_auto_agents_by' => 'id']],
            [['disinfection_manual_by'], 'exist', 'skipOnError' => true, 'targetClass' => RfidTags::className(), 'targetAttribute' => ['disinfection_manual_by' => 'id']],


//            [['tools_by'], 'exist', 'skipOnError' => true, 'targetClass' => Tools::className(), 'targetAttribute' => ['tools_by' => 'id']],
//            [['test_qualities_cleaning_status'], 'exist', 'skipOnError' => true, 'targetClass' => ToolsStatuses::className(), 'targetAttribute' => ['test_qualities_cleaning_status' => 'id']],
//            [['cleaning_agents_by'], 'exist', 'skipOnError' => true, 'targetClass' => ToolsCleaningAgents::className(), 'targetAttribute' => ['cleaning_agents_by' => 'id']],
//            [['test_tightness_by'], 'exist', 'skipOnError' => true, 'targetClass' => RfidTags::className(), 'targetAttribute' => ['test_tightness_by' => 'id']],
//            [['disinfection_auto_by'], 'exist', 'skipOnError' => true, 'targetClass' => ToolsCleaningMachines::className(), 'targetAttribute' => ['disinfection_auto_by' => 'id']],
//            [['disinfection_auto_agents_by'], 'exist', 'skipOnError' => true, 'targetClass' => ToolsCleaningAgents::className(), 'targetAttribute' => ['disinfection_auto_agents_by' => 'id']],
//            [['disinfection_manual_by'], 'exist', 'skipOnError' => true, 'targetClass' => ToolsCleaningAgents::className(), 'targetAttribute' => ['disinfection_manual_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'add_data' => Yii::t('app', 'Add Data'),
            'staff_by' => Yii::t('app', 'Staff By'),
            'tools_by' => Yii::t('app', 'Tools By'),
            'test_tightness_by' => Yii::t('app', 'Leak test'),
            'cleaning_agents_by' => Yii::t('app', 'Cleaning Agents'),
            'cleaning_start' => Yii::t('app', 'Start Cleaning'),
            'cleaning_end' => Yii::t('app', 'End Cleaning'),
            'test_qualities_cleaning_date' => Yii::t('app', 'Date «Cleaning Quality Check»'),
            'test_qualities_cleaning_status' => Yii::t('app', 'Status «Cleaning Quality Check»'),
            'disinfection_type_by' => Yii::t('app', 'Type «Disinfection»'),
            'disinfection_auto_by' => Yii::t('app', 'Auto «Disinfection»'),
            'disinfection_auto_agents_by' => Yii::t('app', 'Cleaning Agents'),
            'disinfection_auto_start' => Yii::t('app', 'Start «Disinfection»'),
            'disinfection_auto_end' => Yii::t('app', 'End «Disinfection»'),
            'disinfection_manual_by' => Yii::t('app', 'Manual «Disinfection»'),
            'disinfection_manual_start' => Yii::t('app', 'Start «Disinfection»'),
            'disinfection_manual_end' => Yii::t('app', 'End «Disinfection»'),
            'cleaning_tools_start' => Yii::t('app', 'Start «Tool cleaning»'),
            'cleaning_tools_end' => Yii::t('app', 'End «Tool cleaning»'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'status_log' => Yii::t('app', 'Status Log'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function findByAll()
    {

        $query = static::find()
            /** Фильтрация Данных "Эндоскопов" */
            ->leftJoin(RfidTags::tableName() . ' as `rfidTools` on `rfidTools` .`id` = ' . self::tableName() . '.`tools_by`')
            ->leftJoin(Tools::tableName() . ' as `tools` on `tools` .`id` = `rfidTools`.`fx_tools`')
            /** Фильтрация Данных "Статусов Тестирования" */
            ->leftJoin(RfidTags::tableName() . ' as `rfidTightness` on `rfidTightness` .`id` = ' . self::tableName() . '.`test_tightness_by`')
            ->leftJoin(ToolsStatuses::tableName() . ' as `statusTightness` on `statusTightness` .`id` = `rfidTightness`.`fx_tools_statuses`')
            /** Фильтрация Данных "Средство Очистки" */
            ->leftJoin(RfidTags::tableName() . ' as `rfidCleaningAgents` on `rfidCleaningAgents` .`id` = ' . self::tableName() . '.`cleaning_agents_by`')
            ->leftJoin(ToolsCleaningAgents::tableName() . ' as `cleaningAgentsBy` on `cleaningAgentsBy` .`id` = `rfidCleaningAgents`.`fx_tools_agents`')
            /** Фильтрация Данных статуса "Проверки качество очистки" */
            ->leftJoin(RfidTags::tableName() . ' as `rfidQualitiesCleaning` on `rfidQualitiesCleaning` .`id` = ' . self::tableName() . '.`test_qualities_cleaning_status`')
            ->leftJoin(ToolsStatuses::tableName() . ' as `statusQualitiesCleaning` on `statusQualitiesCleaning` .`id` = `rfidQualitiesCleaning`.`fx_tools_statuses`')
 /** Фильтрация Данных статуса "Автоматической дезинфекции" */
            ->leftJoin(RfidTags::tableName() . ' as `rfidAutoBy` on `rfidAutoBy` .`id` = ' . self::tableName() . '.`disinfection_auto_by`')
            ->leftJoin(ToolsCleaningMachines::tableName() . ' as `autoBy` on `autoBy` .`id` = `rfidAutoBy`.`fx_tools_machines`')
 /** Фильтрация Данных статуса "Средство очистки в авто режиме" */
            ->leftJoin(RfidTags::tableName() . ' as `rfidAutoAgentsBy` on `rfidAutoAgentsBy` .`id` = ' . self::tableName() . '.`disinfection_auto_agents_by`')
            ->leftJoin(ToolsCleaningAgents::tableName() . ' as `autoAgentsBy` on `autoAgentsBy` .`id` = `rfidAutoAgentsBy`.`fx_tools_agents`')
 /** Фильтрация Данных статуса "Ручной очистки" */
            ->leftJoin(RfidTags::tableName() . ' as `rfidManualBy` on `rfidManualBy` .`id` = ' . self::tableName() . '.`disinfection_manual_by`')
            ->leftJoin(ToolsCleaningAgents::tableName() . ' as `manualBy` on `manualBy` .`id` = `rfidManualBy`.`fx_tools_agents`')
            /** Фильтрация Данных "Сотрудников" */
            ->leftJoin(Staff::tableName() . ' as `staff` on `staff` .`id` = ' . self::tableName() . '.`staff_by`'); // Запустил сотрудник

        //  Скрыть модель со статусом "Удаленные" для всех кроме ROLE_Admin
        /*        if (!(Yii::$app->user->can(AuthItem::OPR_DeleteContent))) {
                    $query->andFilterWhere(['<>', self::tableName() . '.status_system', self::STATUS_SYSTEM_DELETED]);
                }*/

        return $query;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCleaningLevelLog()
    {
        return $this->hasOne(CleaningLevelLog::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToolsBy()
    {
        return $this->hasOne(RfidTags::className(), ['id' => 'tools_by']);
    }

    /**
     * Вернуть список "Средств Очистки"
     * @return array
     */
    public static function getCleaningAgentsList()
    {
        $query = ToolsCleaningAgents::findByAll();

        return ArrayHelper::map($query->all(),'id','name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCleaningAgentsBy()
    {
        return $this->hasOne(RfidTags::className(), ['id' => 'cleaning_agents_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTestQualitiesCleaningStatus()
    {
        return $this->hasOne(RfidTags::className(), ['id' => 'test_qualities_cleaning_status']);
    }

    /**
     * Вернуть список "Сотрудники"
     * @return array
     */
    public static function getStaffList()
    {
        $query = Staff::findByAll();

        return ArrayHelper::map($query->all(),'id','fullName');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffBy()
    {
        return $this->hasOne(Staff::className(), ['id' => 'staff_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTestTightnessBy()
    {
        return $this->hasOne(RfidTags::className(), ['id' => 'test_tightness_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDisinfectionAutoBy()
    {
        return $this->hasOne(RfidTags::className(), ['id' => 'disinfection_auto_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDisinfectionAutoAgentsBy()
    {
        return $this->hasOne(RfidTags::className(), ['id' => 'disinfection_auto_agents_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDisinfectionManualBy()
    {
        return $this->hasOne(RfidTags::className(), ['id' => 'disinfection_manual_by']);
    }


    /**
     * Массив с выражением
     * @return array
     */
    public static function getDisinfectionTypeList()
    {
        return
            [
                self::auto => Yii::t('app', self::auto),
                self::manual => Yii::t('app', self::manual),
            ];
    }

    /**
     * Стиль выражения исходя от его значения
     * @return mixed
     */
    public function getDisinfectionTypeStyle()
    {
        if ($this->disinfection_type_by == self::auto) {
            return '<span class="label label-danger" title="' . Yii::t('app', self::auto) . '">' . Icon::show('magic', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->disinfection_type_by == self::manual) {
            return '<span class="label '.ColorCSS::BG_AQUA.'" title="' . Yii::t('app', self::manual) . '">' . Icon::show('hand-scissors-o', ['class' => 'fa-lg']) . '</span>';
        }
    }

    /**
     * Статус документа в системе
     * @return array
     */
    public function getStatusLogList()
    {
        return [
            self::STATUS_ACTUAL      => Yii::t('app', self::STATUS_ACTUAL),
            self::STATUS_COMPLETED  => Yii::t('app', self::STATUS_COMPLETED),
        ];
    }

    /**
     * Функция вывода стилизованного "status_log"
     * @return string
     */
    public function  getStatusLogStyle()
    {
        if( $this->status_log == self::STATUS_ACTUAL )      {
            return '<span class="label label-warning"   title="'. Yii::t('app', $this->status_log).'">'. Icon::show('battery-2', ['class' => 'fa-lg']) .'</span>';
        }else if( $this->status_log == self::STATUS_COMPLETED )  {
            return '<span class="label label-success"   title="'. Yii::t('app', $this->status_log).'">'. Icon::show('battery-full', ['class' => 'fa-lg']) .'</span>';
        }
    }
}

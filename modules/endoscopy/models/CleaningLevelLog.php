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
 * This is the model class for table "cleaning_level_log".
 *
 * @property int $id
 * @property int|null $add_data Дата добавления
 * @property string $status_rfid Статус пришедшего r-fid
 * @property string $level_1_add_staff_1 Уровень добавления Сотрудника
 * @property string $level_1_add_tools_2 Уровень добавления Эндоскопа
 * @property string $level_2_test_1 Тест на герметичность
 * @property string $level_3_clear_1 Уровень очистки (Если очистка пошла = NOT END, Если завершилась = Yes, Иначе = No)
 * @property string $level_4_test_clear_2 Тест на качество очистки
 * @property string $level_5_disinfection_manual Дезинфекция ручная (Если очистка пошла = NOT END, Если завершилась = Yes, Иначе = No)
 * @property string $level_5_disinfection_auto Дезинфекция автоматическая (Если очистка пошла = NOT END, Если завершилась = Yes, Иначе = No)
 * @property string $level_6_cleaning_tools Очистка инструмента
 * @property string|null $comment_history Комментарии История выполнения
 * @property int $staff_by Сотрудник выполняющий операции
 * @property int|null $updated_at Обновлен
 *
 * @property Staff $staffBy
 * @property CleaningLog $id0
 *
 * @property Staff[] $staffList
 * @property CleaningLevelLog $nextLogLevel
 * @property CleaningLevelLog $nextRfidStatus
 * @property CleaningLevelLog $nextStatusColumn
 * @property CleaningLevelLog[] $statementsTwoList
 * @property CleaningLevelLog[] $statementsThreeList
 * @property CleaningLevelLog[] $level5AutoList
 * @property CleaningLevelLog $level1StaffStyle
 * @property CleaningLevelLog $level1ToolsStyle
 * @property CleaningLevelLog $level2Style
 * @property CleaningLevelLog $level3Style
 * @property CleaningLevelLog $level4Style
 * @property CleaningLevelLog $level5AutoStyle
 * @property CleaningLevelLog $level5ManualStyle
 * @property CleaningLevelLog $level6Style
 */
class CleaningLevelLog extends \yii\db\ActiveRecord
{
    /** Вид формы при отправки */
    const   FORM_TYPE_AJAX = 'ajaxForm';

    /**
     * Сценарии использования модуля Disguise
     */
    const   SCENARIO_UPDATE = 'update',
            SCENARIO_CREATE = 'create',
            SCENARIO_SEARCH = 'search';

    const   YES     = 'Yes',
            NotEND  = 'Not end',
            SelectAgents  = 'Select agents',
            NO      = 'No';

    /**
     * Статус пришедшего r-fid
     */
    public $status_rfid;

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
        return 'cleaning_level_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['staff_by'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],

            [['level_1_add_staff_1', 'level_1_add_tools_2', 'level_2_test_1', 'level_3_clear_1', 'level_4_test_clear_2', 'level_5_disinfection_manual', 'level_5_disinfection_auto', 'level_6_cleaning_tools'], 'string'],
            [['add_data', 'staff_by', 'updated_at'], 'integer'],

            [['level_1_add_staff_1'],  'in', 'range' => array_keys($this->statementsTwoList)],
            [['level_1_add_tools_2'],  'in', 'range' => array_keys($this->statementsTwoList)],
            [['level_2_test_1'],  'in', 'range' => array_keys($this->statementsTwoList)],
            [['level_3_clear_1'],  'in', 'range' => array_keys($this->statementsThreeList)],
            [['level_4_test_clear_2'],  'in', 'range' => array_keys($this->statementsTwoList)],
            [['level_5_disinfection_manual'],  'in', 'range' => array_keys($this->statementsThreeList)],
            [['level_5_disinfection_auto'],  'in', 'range' => array_keys($this->level5AutoList)],
            [['level_6_cleaning_tools'],  'in', 'range' => array_keys($this->statementsTwoList)],

            [['level_1_add_staff_1'],  'default', 'value' => self::NO ],
            [['level_1_add_tools_2'],  'default', 'value' => self::NO ],
            [['level_2_test_1'],  'default', 'value' => self::NO ],
            [['level_3_clear_1'],  'default', 'value' => self::NO ],
            [['level_4_test_clear_2'],  'default', 'value' => self::NO ],
            [['level_5_disinfection_manual'],  'default', 'value' => self::NO ],
            [['level_5_disinfection_auto'],  'default', 'value' => self::NO ],
            [['level_6_cleaning_tools'],  'default', 'value' => self::NO ],


            [['comment_history'], 'string', 'max' => 255],
            [['staff_by'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['staff_by' => 'id']],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => CleaningLog::className(), 'targetAttribute' => ['id' => 'id']],
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
            'level_1_add_staff_1' => Yii::t('app', 'Level 1.1 «Add staff»'),
            'level_1_add_tools_2' => Yii::t('app', 'Level 1.2 «Add tool»'),
            'level_2_test_1' => Yii::t('app', 'Level 2 «Leak test»'),
            'level_3_clear_1' => Yii::t('app', 'Level 3 «Final cleaning»'),
            'level_4_test_clear_2' => Yii::t('app', 'Level 4 «Quality of cleaning»'),
            'level_5_disinfection_manual' => Yii::t('app', 'Level 5 «Manual disinfection»'),
            'level_5_disinfection_auto' => Yii::t('app', 'Level 5 «Auto disinfection»'),
            'level_6_cleaning_tools' => Yii::t('app', 'Level 6 «Tool cleaning»'),
            'comment_history' => Yii::t('app', 'Comment History'),
            'staff_by' => Yii::t('app', 'Staff By'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public static function findByAll()
    {

        $query = static::find()
                /** Фильтрация Данных "Сотрудников" */
                ->leftJoin(Staff::tableName() . ' as `staff` on `staff` .`id` = ' . self::tableName() . '.`staff_by`'); // Запустил сотрудник
        //  Скрыть модель со статусом "Удаленные" для всех кроме ROLE_Admin
        /*        if (!(Yii::$app->user->can(AuthItem::OPR_DeleteContent))) {
                    $query->andFilterWhere(['<>', self::tableName() . '.status_system', self::STATUS_SYSTEM_DELETED]);
                }*/

        return $query;
    }

    /**
     * На каком уровне остановилась запись
     * @return bool|string
     */
    public function getNextLogLevel()
    {
        $typeStatus = null;

        if($this->level_1_add_staff_1 === self::NO){
            $typeStatus = 'level_1_add_staff_1';
        }elseif ($this->level_1_add_tools_2 === self::NO){
            $typeStatus = 'level_1_add_tools_2';
        }elseif ($this->level_2_test_1 === self::NO){
            $typeStatus = 'level_2_test_1';
        }elseif ($this->level_3_clear_1 === self::NO || $this->level_3_clear_1 === self::NotEND){
            $typeStatus = 'level_3_clear_1';
        }elseif ($this->level_4_test_clear_2 === self::NO){
            $typeStatus = 'level_4_test_clear_2';
        }elseif ($this->level_5_disinfection_manual === self::NO && $this->level_5_disinfection_auto === self::NO){
            $typeStatus = 'level_5_disinfection_manual'; // по-умолчанию выбрана ручная очистка
            if($this->status_rfid === RfidTags::Cln_Tool){ $typeStatus = 'level_5_disinfection_auto'; } // если пришел статус метки "Инструмент Очистки", то это автоматическая очистка
        }elseif ($this->level_5_disinfection_manual === self::NotEND){
            $typeStatus = 'level_5_disinfection_manual';
        }elseif ($this->level_5_disinfection_auto === self::NotEND or $this->level_5_disinfection_auto === self::SelectAgents){
            $typeStatus = 'level_5_disinfection_auto';
        }
        /*elseif ($this->level_6_cleaning_tools === self::NO){
            $typeStatus = 'level_6_cleaning_tools';
        }*/

        return $typeStatus;
    }

    /**
     * С каким статусом можно принять r-fid
     * @return bool|string
     */
    public function getNextRfidStatus()
    {
        $typeStatus = null;
        if($this->level_1_add_staff_1 !== self::YES){
            $typeStatus = RfidTags::Staff; // "Сотрудники"
        }elseif($this->level_1_add_tools_2 !== self::YES){
            $typeStatus = RfidTags::Machined; // "Обрабатываемый Инструмент"
        }elseif ($this->level_2_test_1 !== self::YES){
            $typeStatus = RfidTags::Statuses; // "Статусы инструментов" (Да, Нет)
        }elseif ($this->level_3_clear_1 !== self::YES){
            $typeStatus = RfidTags::Cln_Agent; // "Средство Очистки"
        }elseif ($this->level_4_test_clear_2 !== self::YES){
            $typeStatus = RfidTags::Statuses; // "Статусы инструментов" (Да, Нет)
        }elseif ($this->level_5_disinfection_manual === self::NO && $this->level_5_disinfection_auto === self::NO){
            $typeStatus = RfidTags::Cln_Agent; // "Средство Очистки"
            if($this->status_rfid === RfidTags::Cln_Tool){ $typeStatus = RfidTags::Cln_Tool;  } // если пришел статус метки "Инструмент Очистки", то это автоматическая очистка
        }elseif ($this->level_5_disinfection_manual === self::NotEND){
            $typeStatus = RfidTags::Cln_Agent; // "Средство Очистки"
        }elseif ($this->level_5_disinfection_auto === self::NotEND){
            $typeStatus = RfidTags::Cln_Tool; // "Инструмент Очистки"
        }elseif ($this->level_5_disinfection_auto === self::SelectAgents){
            $typeStatus = RfidTags::Cln_Agent; // "Средство Очистки"
        }
/*        else{
            $typeStatus = 'good day';
        }*/

        return $typeStatus;
    }

    /**
     * С каким статусом будет запись следующей колонки
     * @return bool|string
     */
    public function getNextStatusColumn()
    {
        if(($attributeColumnNextLevel = $this->nextLogLevel) === null){
            return null;
        }

        if($this->$attributeColumnNextLevel === self::NotEND && ($attributeColumnNextLevel === "level_3_clear_1" || $attributeColumnNextLevel === "level_5_disinfection_manual")){
            $this->$attributeColumnNextLevel = self::YES;
        }elseif ($this->$attributeColumnNextLevel === self::NO && ($attributeColumnNextLevel === "level_3_clear_1" || $attributeColumnNextLevel === "level_5_disinfection_manual")){
            $this->$attributeColumnNextLevel = self::NotEND;
        }elseif ($this->$attributeColumnNextLevel === self::NO && $attributeColumnNextLevel === "level_5_disinfection_auto"){
            $this->$attributeColumnNextLevel = self::SelectAgents;
        }elseif ($this->$attributeColumnNextLevel === self::SelectAgents && $attributeColumnNextLevel === "level_5_disinfection_auto"){
            $this->$attributeColumnNextLevel = self::NotEND;
        }else{
            $this->$attributeColumnNextLevel = self::YES;
        }

        return $this->$attributeColumnNextLevel;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(CleaningLog::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffBy()
    {
        return $this->hasOne(Staff::className(), ['id' => 'staff_by']);
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
     * Массив с выражением
     * @return array
     */
    public static function getStatementsTwoList()
    {
        return
            [
                self::YES => Yii::t('app', self::YES),
                self::NO => Yii::t('app', self::NO),
            ];
    }

    /**
     * Массив с выражением
     * @return array
     */
    public static function getStatementsThreeList()
    {
        return
            [
                self::YES => Yii::t('app', self::YES),
                self::NO => Yii::t('app', self::NO),
                self::NotEND => Yii::t('app', self::NotEND),
            ];
    }

    /**
     * Массив с выражением
     * @return array
     */
    public static function getLevel5AutoList()
    {
        return
            [
                self::YES => Yii::t('app', self::YES),
                self::NO => Yii::t('app', self::NO),
                self::NotEND => Yii::t('app', self::NotEND),
                self::SelectAgents => Yii::t('app', self::SelectAgents),
            ];
    }

    /**
     * Стиль выражения исходя от его значения
     * @return mixed
     */
    public function getLevel1StaffStyle()
    {
        if ($this->level_1_add_staff_1 == self::YES) {
            return '<span class="label '.ColorCSS::BG_GREEN.'" title="' . Yii::t('app', self::YES) . '">' . Icon::show('check', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->level_1_add_staff_1 == self::NO) {
            return '<span class="label '.ColorCSS::BG_RED_ACTIVE.'" title="' . Yii::t('app', self::NO) . '">' . Icon::show('remove', ['class' => 'fa-lg']) . '</span>';
        }
    }

    /**
     * Стиль выражения исходя от его значения
     * @return mixed
     */
    public function getLevel1ToolsStyle()
    {
        if ($this->level_1_add_tools_2 == self::YES) {
            return '<span class="label '.ColorCSS::BG_GREEN.'" title="' . Yii::t('app', self::YES) . '">' . Icon::show('check', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->level_1_add_tools_2 == self::NO) {
            return '<span class="label '.ColorCSS::BG_RED_ACTIVE.'" title="' . Yii::t('app', self::NO) . '">' . Icon::show('remove', ['class' => 'fa-lg']) . '</span>';
        }
    }

    /**
     * Стиль выражения исходя от его значения
     * @return mixed
     */
    public function getLevel2Style()
    {
        if ($this->level_2_test_1 == self::YES) {
            return '<span class="label '.ColorCSS::BG_GREEN.'" title="' . Yii::t('app', self::YES) . '">' . Icon::show('check', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->level_2_test_1 == self::NO) {
            return '<span class="label '.ColorCSS::BG_RED_ACTIVE.'" title="' . Yii::t('app', self::NO) . '">' . Icon::show('remove', ['class' => 'fa-lg']) . '</span>';
        }
    }

    /**
     * Стиль выражения исходя от его значения
     * @return mixed
     */
    public function getLevel3Style()
    {
        if ($this->level_3_clear_1 == self::YES) {
            return '<span class="label '.ColorCSS::BG_GREEN.'" title="' . Yii::t('app', self::YES) . '">' . Icon::show('check', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->level_3_clear_1 == self::NO) {
            return '<span class="label '.ColorCSS::BG_RED_ACTIVE.'" title="' . Yii::t('app', self::NO) . '">' . Icon::show('remove', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->level_3_clear_1 == self::NotEND) {
            return '<span class="label '.ColorCSS::BG_AQUA.'" title="' . Yii::t('app', self::NotEND) . '">' . Icon::show('random', ['class' => 'fa-lg']) . '</span>';
        }
    }

    /**
     * Стиль выражения исходя от его значения
     * @return mixed
     */
    public function getLevel4Style()
    {
        if ($this->level_4_test_clear_2 == self::YES) {
            return '<span class="label '.ColorCSS::BG_GREEN.'" title="' . Yii::t('app', self::YES) . '">' . Icon::show('check', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->level_4_test_clear_2 == self::NO) {
            return '<span class="label '.ColorCSS::BG_RED_ACTIVE.'" title="' . Yii::t('app', self::NO) . '">' . Icon::show('remove', ['class' => 'fa-lg']) . '</span>';
        }
    }

    /**
     * Стиль выражения исходя от его значения
     * @return mixed
     */
    public function getLevel5AutoStyle()
    {
        if ($this->level_5_disinfection_auto == self::YES) {
            return '<span class="label '.ColorCSS::BG_GREEN.'" title="' . Yii::t('app', self::YES) . '">' . Icon::show('check', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->level_5_disinfection_auto == self::NO) {
            return '<span class="label '.ColorCSS::BG_RED_ACTIVE.'" title="' . Yii::t('app', self::NO) . '">' . Icon::show('remove', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->level_5_disinfection_auto == self::NotEND) {
            return '<span class="label '.ColorCSS::BG_AQUA.'" title="' . Yii::t('app', self::NotEND) . '">' . Icon::show('random', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->level_5_disinfection_auto == self::SelectAgents) {
            return '<span class="label '.ColorCSS::BG_FUCHSIA.'" title="' . Yii::t('app', self::SelectAgents) . '">' . Icon::show('flask', ['class' => 'fa-lg']) . '</span>';
        }
    }

    /**
     * Стиль выражения исходя от его значения
     * @return mixed
     */
    public function getLevel5ManualStyle()
    {
        if ($this->level_5_disinfection_manual == self::YES) {
            return '<span class="label '.ColorCSS::BG_GREEN.'" title="' . Yii::t('app', self::YES) . '">' . Icon::show('check', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->level_5_disinfection_manual == self::NO) {
            return '<span class="label '.ColorCSS::BG_RED_ACTIVE.'" title="' . Yii::t('app', self::NO) . '">' . Icon::show('remove', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->level_5_disinfection_manual == self::NotEND) {
            return '<span class="label '.ColorCSS::BG_AQUA.'" title="' . Yii::t('app', self::NotEND) . '">' . Icon::show('random', ['class' => 'fa-lg']) . '</span>';
        }
    }

    /**
     * Стиль выражения исходя от его значения
     * @return mixed
     */
    public function getLevel6Style()
    {
        if ($this->level_6_cleaning_tools == self::YES) {
            return '<span class="label '.ColorCSS::BG_GREEN.'" title="' . Yii::t('app', self::YES) . '">' . Icon::show('check', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->level_6_cleaning_tools == self::NO) {
            return '<span class="label '.ColorCSS::BG_RED_ACTIVE.'" title="' . Yii::t('app', self::NO) . '">' . Icon::show('remove', ['class' => 'fa-lg']) . '</span>';
        }
    }
}

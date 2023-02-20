<?php

namespace app\modules\endoscopy\models;

use Yii;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\base\Event;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

use yii\caching\TagDependency;

use kartik\icons\Icon;

use igor162\adminlte\ColorCSS;

use app\widgets\actions\Helper;

use app\modules\security\models\User;
use app\modules\staff\models\Staff;
use app\modules\security\models\AuthItem;

/**
 * This is the model class for table "rfid_tags".
 *
 * @property int $id
 * @property int $coded_key Закодированный Ключ метки
 * @property string|null $status_tied Статус Привязки
 * @property string $fixed_by Метка привязана к объекту
 * @property int|null $fx_tools_agents Средство очистки
 * @property int|null $fx_tools_machines Машина очистки
 * @property int|null $fx_tools_statuses Статусы
 * @property int|null $fx_tools Эндоскопы
 * @property int|null $fx_staff Сотрудник
 * @property string|null $comment Комментарии
 * @property int|null $created_by Добавил
 * @property int|null $created_at Создан
 * @property int|null $updated_by Обновил
 * @property int|null $updated_at Обновлен
 *
 *
 * @property RfidTags $fixedBy
 * @property ToolsCleaningAgents $fxToolsAgents
 * @property ToolsCleaningMachines $fxToolsMachines
 * @property ToolsStatuses $fxToolsStatuses
 * @property Tools $fxTools
 * @property Staff $fxStaff
 *
 * @property RfidTags[] $statusTiedList
 * @property ToolsCleaningAgents[] $agentsList
 * @property ToolsCleaningMachines[] $machinesList
 * @property ToolsStatuses[] $statusesList
 * @property Tools[] $toolsList
 * @property Staff[] $staffList
 *
 * @property Staff $createdBy
 * @property Staff $updatedBy
 * @property Staff[] $usersStaff
 * @property Users[] $usersList
 */
class RfidTags extends \yii\db\ActiveRecord
{

    public $fixed_by; // Метка привязана к объекту

    /** Вид формы при отправки */
    const   FORM_TYPE_AJAX = 'ajaxForm';

    /**
     * Сценарии использования модуля Disguise
     */
    const   SCENARIO_UPDATE = 'update',
            SCENARIO_CREATE = 'create',
            SCENARIO_SEARCH = 'search';

    const  /* YES = 'Yes',*/
            No  = 'No',
            Cln_Agent = 'Cleaning Agent', // "Средство Очистки"
            Cln_Tool  = 'Cleaning Tool', // "Инструмент Очистки"
            Machined  = 'Machined Tool', // "Обрабатываемый Инструмент"
            Statuses  = 'Instrument Statuses', // "Статусы инструментов"
            Staff     = 'Staff'; // "Сотрудники"

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

    /** Запуск действий перед выполнением модели */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_BEFORE_UPDATE, [$this, 'beforeChange']); // До обноления
        $this->on(self::EVENT_BEFORE_INSERT, [$this, 'beforeChange']); // До вставки
    }

    /**
     * @param Event $event
     * @return string
     */
    public function beforeChange(Event $event){
        // Изменить Статус Связей
        if(empty($this->fx_tools_agents) && empty($this->fx_tools_machines) && empty($this->fx_tools_statuses) && empty($this->fx_tools) && empty($this->fx_staff)) {
            return $this->status_tied = self::No;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'rfid_tags';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coded_key', 'status_tied'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],

            [['fx_staff', 'fixed_by', 'coded_key', 'created_by', 'created_at', 'updated_by', 'updated_at'], 'integer'],
            [['status_tied'], 'string'],
            [['comment'], 'string', 'max' => 255],

            [['coded_key'],  'unique', 'targetClass' => $this::className(), 'message' => \Yii::t('app', 'This «{attribute}» is already in use', ['attribute' => \Yii::t('app', 'Rfid Tag')] )],

//            [['status_tied'],   'default', 'value' => self::No],
            [['status_tied'], 'validateStatuses'],
            [['status_tied'],  'in', 'range' => array_keys($this->statusTiedList) ],

            [['fx_tools_agents'], 'exist', 'skipOnError' => true, 'targetClass' => ToolsCleaningAgents::className(), 'targetAttribute' => ['fx_tools_agents' => 'id']],
            [['fx_tools_machines'], 'exist', 'skipOnError' => true, 'targetClass' => ToolsCleaningMachines::className(), 'targetAttribute' => ['fx_tools_machines' => 'id']],
            [['fx_tools_statuses'], 'exist', 'skipOnError' => true, 'targetClass' => ToolsStatuses::className(), 'targetAttribute' => ['fx_tools_statuses' => 'id']],
            [['fx_tools'], 'exist', 'skipOnError' => true, 'targetClass' => Tools::className(), 'targetAttribute' => ['fx_tools' => 'id']],
            [['fx_staff'], 'exist', 'skipOnError' => true, 'targetClass' => Staff::className(), 'targetAttribute' => ['fx_staff' => 'id']],

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
            'coded_key' => Yii::t('app', 'Coded Key r-fid'),
            'status_tied' => Yii::t('app', 'Status Tied'),
            'fixed_by' => Yii::t('app', 'Fixed By'),
            'comment' => Yii::t('app', 'Comment'),
            'fx_tools_agents' => Yii::t('app', 'Cleaning Agents'),
            'fx_tools_machines' => Yii::t('app', 'Cleaning Tools'),
            'fx_tools_statuses' => Yii::t('app', 'Instrument Statuses'),
            'fx_tools' => Yii::t('app', 'Machined Tool'),
            'fx_staff' => Yii::t('app', 'Staff'),
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

            ->leftJoin(ToolsCleaningAgents::tableName() . ' as `fxAgents` on `fxAgents` .`id` = ' . self::tableName() . '.`fx_tools_agents`') // Создал
            ->leftJoin(ToolsCleaningMachines::tableName() . ' as `fxMachines` on `fxMachines` .`id` = ' . self::tableName() . '.`fx_tools_machines`') // Создал
            ->leftJoin(ToolsStatuses::tableName() . ' as `fxStatus` on `fxStatus` .`id` = ' . self::tableName() . '.`fx_tools_statuses`') // Создал
            ->leftJoin(Tools::tableName() . ' as `fxTools` on `fxTools` .`id` = ' . self::tableName() . '.`fx_tools`') // Создал
            ->leftJoin(Staff::tableName() . ' as `fxStaff` on `fxStaff` .`id` = ' . self::tableName() . '.`fx_staff`') // Создал


            ->leftJoin(Staff::tableName() . ' as `create` on `create` .`id` = ' . self::tableName() . '.`created_by`') // Создал
            ->leftJoin(Staff::tableName() . ' as `update` on `update` .`id` = ' . self::tableName() . '.`updated_by`'); // Обновил

        //  Скрыть модель со статусом "Удаленные" для всех кроме ROLE_Admin
/*        if (!(Yii::$app->user->can(AuthItem::OPR_DeleteContent))) {
            $query->andFilterWhere(['<>', self::tableName() . '.status_system', self::STATUS_SYSTEM_DELETED]);
        }*/

        return $query;
    }

    /**
     * Функция проверки выбранного статуса "status_tied"
     * @param $attribute
     * @param $params
     */
    public function validateStatuses($attribute, $params)
    {
        switch ($this->$attribute){
            case self::Cln_Agent:
                if(!$this->fx_tools_agents){
                    $this->addError('fx_tools_agents', Yii::t('app', 'Choose the «{attribute}».', ['attribute'=>$this->getAttributeLabel('fx_tools_agents')]));
                }
                // Очистить другие значения
                $this->fx_tools_machines = null; $this->fx_tools = null; $this->fx_tools_statuses = null; $this->fx_staff = null;
                break;
            case self::Cln_Tool:
                if(!$this->fx_tools_machines){
                    $this->addError('fx_tools_machines', Yii::t('app', 'Choose the «{attribute}».', ['attribute'=>$this->getAttributeLabel('fx_tools_machines')]));
                }
                // Очистить другие значения
                $this->fx_tools_agents = null; $this->fx_tools = null; $this->fx_tools_statuses = null; $this->fx_staff = null;
                break;
            case self::Machined:
                if(!$this->fx_tools){
                    $this->addError('fx_tools', Yii::t('app', 'Choose the «{attribute}».', ['attribute'=>$this->getAttributeLabel('fx_tools')]));
                }
                // Очистить другие значения
                $this->fx_tools_agents = null; $this->fx_tools_machines = null; $this->fx_tools_statuses = null; $this->fx_staff = null;
                break;
            case self::Statuses:
                if(!$this->fx_tools_statuses){
                    $this->addError('fx_tools_statuses', Yii::t('app', 'Choose the «{attribute}».', ['attribute'=>$this->getAttributeLabel('fx_tools_statuses')]));
                }
                // Очистить другие значения
                $this->fx_tools_agents = null; $this->fx_tools_machines = null; $this->fx_tools = null; $this->fx_staff = null;
                break;
            case self::Staff:
                if(!$this->fx_staff){
                    $this->addError('fx_staff', Yii::t('app', 'Choose the «{attribute}».', ['attribute'=>$this->getAttributeLabel('fx_staff')]));
                }
                // Очистить другие значения
                $this->fx_tools_agents = null; $this->fx_tools_machines = null; $this->fx_tools = null; $this->fx_tools_statuses = null;
                break;
        }
    }

    /**
     * Массив с выражением
     * @return array
     */
    public static function getStatusTiedList()
    {
        return
            [
                self::No => Yii::t('app', self::No),
                self::Cln_Agent => Yii::t('app', self::Cln_Agent),
                self::Cln_Tool => Yii::t('app', self::Cln_Tool),
                self::Machined => Yii::t('app', self::Machined),
                self::Statuses => Yii::t('app', self::Statuses),
                self::Staff => Yii::t('app', self::Staff),
            ];
    }

    /**
     * Стиль выражения исходя от его значения
     * @return mixed
     */
    public function getStatusTiedStyle()
    {
        if ($this->status_tied == self::No) {
            return '<span class="label label-danger" title="' . Yii::t('app', self::No) . '">' . Icon::show('remove', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->status_tied == self::Cln_Agent) {
            return '<span class="label '.ColorCSS::BG_AQUA.'" title="' . Yii::t('app', self::Cln_Agent) . '">' . Icon::show('flask', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->status_tied == self::Cln_Tool) {
            return '<span class="label '.ColorCSS::BG_GREEN.'" title="' . Yii::t('app', self::Cln_Tool) . '">' . Icon::show('shower', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->status_tied == self::Machined) {
            return '<span class="label '.ColorCSS::BG_FUCHSIA.'" title="' . Yii::t('app', self::Machined) . '">' . Icon::show('gears', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->status_tied == self::Statuses) {
            return '<span class="label '.ColorCSS::BG_BLUE.'" title="' . Yii::t('app', self::Statuses) . '">' . Icon::show('bell', ['class' => 'fa-lg']) . '</span>';
        } else if ($this->status_tied == self::Staff) {
            return '<span class="label '.ColorCSS::BG_PURPLE.'" title="' . Yii::t('app', self::Staff) . '">' . Icon::show('users', ['class' => 'fa-lg']) . '</span>';
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFxToolsAgents()
    {
        return $this->hasOne(ToolsCleaningAgents::className(), ['id' => 'fx_tools_agents']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFxToolsMachines()
    {
        return $this->hasOne(ToolsCleaningMachines::className(), ['id' => 'fx_tools_machines']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFxToolsStatuses()
    {
        return $this->hasOne(ToolsStatuses::className(), ['id' => 'fx_tools_statuses']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFxTools()
    {
        return $this->hasOne(Tools::className(), ['id' => 'fx_tools']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFxStaff()
    {
        return $this->hasOne(Staff::className(), ['id' => 'fx_staff']);
    }

    /**
     * Вывести к кому привязана Метка RFID
     * @return null|string
     */
    public function getFixedBy()
    {
        if(($m = $this->fxStaff)!== null){ // Вывести Сотрудника привязанного
                return Html::a(Html::encode($m->last_name), ['/staff/staff/update/', 'id' => Html::encode($m->id), 'returnUrl' => Helper::getReturnUrl()], ['title' => \Yii::t('app', 'view data of «{attribute}»', ['attribute' => \Yii::t('app', 'stafF')]), 'class'=> 'label'. ' ' . ColorCSS::BG_PURPLE]);
        }elseif(($m = $this->fxTools)!== null){ // Вывести привязанный Эндоскоп
            return Html::a(Html::encode($m->name), ['tools/update/', 'id' => Html::encode($m->id), 'returnUrl' => Helper::getReturnUrl()], ['title' => \Yii::t('app', 'view data of «{attribute}»', ['attribute' => \Yii::t('app', 'TooL')]), 'class'=> 'label'. ' ' . ColorCSS::BG_GREEN]);
        }elseif(($m = $this->fxToolsAgents)!== null){ // Вывести привязанное средство очистки
            return Html::a(Html::encode($m->name), ['tools-cleaning-agents/update/', 'id' => Html::encode($m->id), 'returnUrl' => Helper::getReturnUrl()], ['title' => \Yii::t('app', 'view data of «{attribute}»', ['attribute' => \Yii::t('app', 'Cleaning Agents')]), 'class'=> 'label'. ' ' . ColorCSS::BG_AQUA]);
        }elseif(($m = $this->fxToolsMachines)!== null){ // Вывести привязанный инструмент очистки
            return Html::a(Html::encode($m->name), ['tools-cleaning-machines/update/', 'id' => Html::encode($m->id), 'returnUrl' => Helper::getReturnUrl()], ['title' => \Yii::t('app', 'view data of «{attribute}»', ['attribute' => \Yii::t('app', 'Cleaning tool')]), 'class'=> 'label'. ' ' . ColorCSS::BG_FUCHSIA]);
        }elseif(($m = $this->fxToolsStatuses)!== null){ // Вывести привязанный статус очистки
            return Html::a(Html::encode($m->name), ['tools-statuses/update/', 'id' => Html::encode($m->id), 'returnUrl' => Helper::getReturnUrl()], ['title' => \Yii::t('app', 'view data of «{attribute}»', ['attribute' => \Yii::t('app', 'STatus')]), 'class'=> 'label'. ' ' . ColorCSS::BG_BLUE]);
        }else{
            return null;
        }
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
    public function getUsersStaff()
    {
        return $this->hasMany(Staff::className(), ['coded_key' => 'id']);
    }

    /**
     * Вернуть список "Пользователей" доступных для авторизованного пользователя
     * @return array
     */
    public static function getUsersList()
    {
        $query = User::find();

        //  Показывать "Пользователей" своей группы для всех, кроме ROLE_Admin
        if (!(Yii::$app->user->can(AuthItem::ROLE_Admin))) {
            $query->andWhere(['!=', 'status_system', User::STATUS_SYSTEM_DELETED]);
        }

        return ArrayHelper::map($query->all(), 'id', 'username');
    }

    /**
     * Вернуть список "Средства Очистки"
     * @return array
     */
    public static function getAgentsList()
    {
        $query = ToolsCleaningAgents::findByAll();

        return ArrayHelper::map($query->all(),'id','name');
    }

    /**
     * Вернуть список "Инструменты Очистки"
     * @return array
     */
    public static function getMachinesList()
    {
        $query = ToolsCleaningMachines::findByAll();

        return ArrayHelper::map($query->all(),'id','name');
    }

    /**
     * Вернуть список "Статусы Инструментов"
     * @return array
     */
    public static function getStatusesList()
    {
        $query = ToolsStatuses::findByAll();

        return ArrayHelper::map($query->all(),'id','name');
    }

    /**
     * Вернуть список "Список Обрабатываемых инструментов"
     * @return array
     */
    public static function getToolsList()
    {
        $query = Tools::findByAll();

        return ArrayHelper::map($query->all(),'id','name');
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
}

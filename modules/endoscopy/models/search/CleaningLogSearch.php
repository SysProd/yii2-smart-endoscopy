<?php

namespace app\modules\endoscopy\models\search;

use app\modules\endoscopy\models\Tools;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

use app\modules\endoscopy\models\CleaningLog;

/**
 * CleaningLogSearch represents the model behind the search form of `app\modules\endoscopy\models\CleaningLog`.
 *
 * @property integer $created_at_from
 * @property integer $created_at_till
 * @property integer $updated_at_from
 * @property integer $updated_at_till
 */
class CleaningLogSearch extends CleaningLog
{
    const COUNT = 20; // количество элементов на одной странице

    public $created_at_from; // начало диапазона поиска по дате создания
    public $created_at_till; // конец диапазона поиска по дате создания
    public $updated_at_from; // начало диапазона поиска по дате обновления
    public $updated_at_till; // конец диапазона поиска по дате обновления

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer', 'on' => self::SCENARIO_SEARCH],

            [['created_at_from', 'created_at_till', 'updated_at_from', 'updated_at_till', 'add_data', 'updated_at'], 'date', 'format' => 'dd-mm-yy', 'on' => self::SCENARIO_SEARCH],

            [['disinfection_type_by', 'tools_by', 'test_tightness_by', 'cleaning_agents_by', 'cleaning_start', 'cleaning_end', 'test_qualities_cleaning_date', 'test_qualities_cleaning_status', 'disinfection_auto_by', 'disinfection_auto_agents_by', 'disinfection_auto_start', 'disinfection_auto_end', 'disinfection_manual_by', 'disinfection_manual_start', 'disinfection_manual_end', 'cleaning_tools_start', 'cleaning_tools_end', 'staff_by', 'status_log',], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Наименования дополнительных полей
     * аттрибутов, присущих модели поиска
     * @return array
     */
    public function attributeLabels()
    {
        $label = parent::attributeLabels();

        $newLabel = [
            'created_at_from' => Yii::t('app', 'Created at from'),
            'created_at_till' => Yii::t('app', 'Created at till'),
            'updated_at_from' => Yii::t('app', 'Updated at from'),
            'updated_at_till' => Yii::t('app', 'Updated at till'),
        ];

        return ArrayHelper::merge($label, $newLabel);
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->scenario = self::SCENARIO_SEARCH; // Устанавливаем сценарий поиска

        $query = CleaningLog::findByAll();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this::COUNT,
            ],
            // Сортировка по умолчанию
            'sort' => array(
                'defaultOrder' => ['created_at' => SORT_DESC],
            ),
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'add_data',
                'tools_by' =>
                    [
                        'asc'       => [ 'tools.name' => SORT_ASC ],
                        'desc'      => [ 'tools.name' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
                'test_tightness_by' =>
                    [
                        'asc'       => [ 'statusTightness.name' => SORT_ASC ],
                        'desc'      => [ 'statusTightness.name' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
                'cleaning_agents_by' =>
                    [
                        'asc'       => [ 'cleaningAgentsBy.name' => SORT_ASC ],
                        'desc'      => [ 'cleaningAgentsBy.name' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
                'cleaning_start',
                'cleaning_end',
                'test_qualities_cleaning_date',
                'test_qualities_cleaning_status' =>
                    [
                        'asc'       => [ 'statusQualitiesCleaning.name' => SORT_ASC ],
                        'desc'      => [ 'statusQualitiesCleaning.name' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
                'disinfection_type_by',
                'disinfection_auto_by' =>
                    [
                        'asc'       => [ 'autoBy.name' => SORT_ASC ],
                        'desc'      => [ 'autoBy.name' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
                'disinfection_auto_agents_by' =>
                    [
                        'asc'       => [ 'autoAgentsBy.name' => SORT_ASC ],
                        'desc'      => [ 'autoAgentsBy.name' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
                'disinfection_auto_start',
                'disinfection_auto_end',
                'disinfection_manual_by' =>
                    [
                        'asc'       => [ 'manualBy.name' => SORT_ASC ],
                        'desc'      => [ 'manualBy.name' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
                'disinfection_manual_start',
                'disinfection_manual_end',
                'cleaning_tools_start',
                'cleaning_tools_end',
                'updated_at',
                'status_log',
                'staff_by' =>
                    [
                        'asc'       => [ 'staff.last_name' => SORT_ASC ],
                        'desc'      => [ 'staff.last_name' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
/*                'updated_by' =>
                    [
                        'asc'       => [ 'update.last_name' => SORT_ASC ],
                        'desc'      => [ 'update.last_name' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],*/
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // Раскомментируйте следующую строку, если вы не хотите возвращать какие-либо записи при неудачной проверке
            $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.cleaning_start),"%d-%m-%Y")' => $this->cleaning_start,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.cleaning_end),"%d-%m-%Y")' => $this->cleaning_end,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.test_qualities_cleaning_date),"%d-%m-%Y")' => $this->test_qualities_cleaning_date,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.disinfection_auto_start),"%d-%m-%Y")' => $this->disinfection_auto_start,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.disinfection_auto_end),"%d-%m-%Y")' => $this->disinfection_auto_end,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.disinfection_manual_start),"%d-%m-%Y")' => $this->disinfection_manual_start,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.disinfection_manual_end),"%d-%m-%Y")' => $this->disinfection_manual_end,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.cleaning_tools_start),"%d-%m-%Y")' => $this->cleaning_tools_start,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.cleaning_tools_end),"%d-%m-%Y")' => $this->cleaning_tools_end,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.add_data),"%d-%m-%Y")' => $this->add_data,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.updated_at),"%d-%m-%Y")' => $this->updated_at,
            'disinfection_type_by' => $this->disinfection_type_by,
            'status_log' => $this->status_log,
        ]);

        $query  ->andFilterWhere(['like', 'tools.name', $this->tools_by])
                ->andFilterWhere(['like', 'statusTightness.name', $this->test_tightness_by])
                ->andFilterWhere(['like', 'cleaningAgentsBy.name', $this->cleaning_agents_by])
                ->andFilterWhere(['like', 'statusQualitiesCleaning.name', $this->test_qualities_cleaning_status])
                ->andFilterWhere(['like', 'autoBy.name', $this->disinfection_auto_by])
                ->andFilterWhere(['like', 'autoAgentsBy.name', $this->disinfection_auto_agents_by])
                ->andFilterWhere(['like', 'manualBy.name', $this->disinfection_manual_by])
                ->andFilterWhere(['like', 'staff.last_name', $this->staff_by]);

        if ($this->created_at_from && $this->created_at_till) { $query->andFilterWhere(['between', self::tableName().'.add_data', strtotime($this->created_at_from), strtotime($this->created_at_till)]); }
        if ($this->updated_at_from && $this->updated_at_till) { $query->andFilterWhere(['between', self::tableName().'.updated_at', strtotime($this->updated_at_from), strtotime($this->updated_at_till)]); }

        return $dataProvider;
    }
}

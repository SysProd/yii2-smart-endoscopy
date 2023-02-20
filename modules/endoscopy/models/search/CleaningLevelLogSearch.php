<?php

namespace app\modules\endoscopy\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

use app\modules\endoscopy\models\CleaningLevelLog;

/**
 * CleaningLevelLogSearch represents the model behind the search form of `app\modules\endoscopy\models\CleaningLevelLog`.
 *
 * @property integer $created_at_from
 * @property integer $created_at_till
 * @property integer $updated_at_from
 * @property integer $updated_at_till
 *
 */
class CleaningLevelLogSearch extends CleaningLevelLog
{
    const COUNT = 20; // количество элементов на одной странице

    public $updated_at_from; // начало диапазона поиска по дате обновления
    public $updated_at_till; // конец диапазона поиска по дате обновления

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer', 'on' => self::SCENARIO_SEARCH],

            [['updated_at_from', 'updated_at_till', 'updated_at', 'add_data',], 'date', 'format' => 'dd-mm-yy', 'on' => self::SCENARIO_SEARCH],

            [['staff_by','staff_by', 'level_1_add_staff_1', 'level_1_add_tools_2', 'level_2_test_1', 'level_3_clear_1', 'level_4_test_clear_2', 'level_5_disinfection_manual', 'level_5_disinfection_auto', 'level_6_cleaning_tools', 'comment_history'], 'safe'],
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

        $query = CleaningLevelLog::findByAll();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this::COUNT,
            ],
            // Сортировка по умолчанию
            'sort' => array(
                'defaultOrder' => ['updated_at' => SORT_DESC],
            ),
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'add_data',
                'level_1_add_staff_1',
                'level_1_add_tools_2',
                'level_2_test_1',
                'level_3_clear_1',
                'level_4_test_clear_2',
                'level_5_disinfection_manual',
                'level_5_disinfection_auto',
                'level_6_cleaning_tools',
                'comment_history',
                'updated_at',
                'staff_by' =>
                    [
                        'asc'       => [ 'staff.last_name' => SORT_ASC ],
                        'desc'      => [ 'staff.last_name' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
                'updated_by' =>
                    [
                        'asc'       => [ 'update.last_name' => SORT_ASC ],
                        'desc'      => [ 'update.last_name' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // Раскомментируйте следующую строку, если вы не хотите возвращать какие-либо записи при неудачной проверке
            $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query ->andFilterWhere([
            'id' => $this->id,
            'level_1_add_staff_1' => $this->level_1_add_staff_1,
            'level_1_add_tools_2' => $this->level_1_add_tools_2,
            'level_2_test_1' => $this->level_2_test_1,
            'level_3_clear_1' => $this->level_3_clear_1,
            'level_4_test_clear_2' => $this->level_4_test_clear_2,
            'level_5_disinfection_manual' => $this->level_5_disinfection_manual,
            'level_5_disinfection_auto' => $this->level_5_disinfection_auto,
            'level_6_cleaning_tools' => $this->level_6_cleaning_tools,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.add_data),"%d-%m-%Y")' => $this->add_data,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.updated_at),"%d-%m-%Y")' => $this->updated_at,
        ]);

        $query  ->andFilterWhere(['like', 'comment_history', $this->comment_history])
                ->andFilterWhere(['like', 'staff.last_name', $this->staff_by]);

        if ($this->updated_at_from && $this->updated_at_till) { $query->andFilterWhere(['between', self::tableName().'.updated_at', strtotime($this->updated_at_from), strtotime($this->updated_at_till)]); }

        return $dataProvider;
    }
}

<?php

namespace app\modules\endoscopy\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

use app\modules\endoscopy\models\ToolsCleaningAgents;

/**
 * ToolsCleaningAgentsSearch represents the model behind the search form of `app\modules\endoscopy\models\ToolsCleaningAgents`.
 *
 * @property integer $created_at_from
 * @property integer $created_at_till
 * @property integer $updated_at_from
 * @property integer $updated_at_till
 *
 */
class ToolsCleaningAgentsSearch extends ToolsCleaningAgents
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
            [['id', 'temp', ], 'integer'],

            [['created_at_from', 'created_at_till', 'updated_at_from', 'updated_at_till', 'created_at', 'updated_at'], 'date', 'format' => 'dd-mm-yy', 'on' => self::SCENARIO_SEARCH],

            [['name', 'concentration', 'comment', 'created_by', 'updated_by', ], 'safe'],

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

        $query = ToolsCleaningAgents::findByAll();

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
                'name',
                'concentration',
                'temp',
                'comment',
                'created_at',
                'updated_at',
                'created_by' =>
                    [
                        'asc'       => [ 'create.username' => SORT_ASC ],
                        'desc'      => [ 'create.username' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
                'updated_by' =>
                    [
                        'asc'       => [ 'update.username' => SORT_ASC ],
                        'desc'      => [ 'update.username' => SORT_DESC ],
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
        $query->andFilterWhere([
            'id' => $this->id,
            'temp' => $this->temp,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.created_at),"%d-%m-%Y")' => $this->created_at,
            'DATE_FORMAT(FROM_UNIXTIME('.self::tableName().'.updated_at),"%d-%m-%Y")' => $this->updated_at,
        ]);

        $query  ->andFilterWhere(['like', 'name', $this->name])
                ->andFilterWhere(['like', 'concentration', $this->concentration])
                ->andFilterWhere(['like', 'create.username',        $this->created_by])
                ->andFilterWhere(['like', 'update.username',        $this->updated_by]);

        if ($this->created_at_from && $this->created_at_till) { $query->andFilterWhere(['between', self::tableName().'.created_at', strtotime($this->created_at_from), strtotime($this->created_at_till)]); }
        if ($this->updated_at_from && $this->updated_at_till) { $query->andFilterWhere(['between', self::tableName().'.updated_at', strtotime($this->updated_at_from), strtotime($this->updated_at_till)]); }

        return $dataProvider;
    }
}

<?php

namespace app\modules\staff\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

use app\modules\staff\models\Staff;
use app\modules\counterparty\models\Company;
use app\modules\counterparty\models\Counterparty;
use app\modules\counterparty\models\Functions;
use app\modules\data\models\Phone;

/**
 * StaffSearch represents the model behind the search form about `app\modules\staff\models\Staff`.
 *
 * @property integer $id_from
 * @property integer $id_till
 * @property string $fullName
 * @property string $phoneDefault
 * @property integer $created_at_from
 * @property integer $created_at_till
 * @property integer $updated_at_from
 * @property integer $updated_at_till
 */
class StaffSearch extends Staff
{
    const COUNT = 20; // количество элементов на одной странице

    public $id_from; // начало диапазона поиска по ID
    public $id_till; // конец диапазона поиска по ID
    public $created_at_from; // начало диапазона поиска по дате создания
    public $created_at_till; // конец диапазона поиска по дате создания
    public $updated_at_from; // начало диапазона поиска по дате обновления
    public $updated_at_till; // конец диапазона поиска по дате обновления

    public $fullName, $phoneDefault;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],

            [['email'], 'email'],

            [['first_name', 'last_name', 'patronymic', 'gender', 'email', 'fullName', 'phoneDefault', 'created_by', 'updated_by'], 'string', 'max' => 30],

            [['created_at', 'updated_at'], 'date', 'format'=>'dd-mm-yy'],
        ];
    }

    /**
     * @inheritdoc
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
            'id_from' => Yii::t('app', 'ID From'),
            'id_till' => Yii::t('app', 'ID Till'),
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

        $query = Staff::findByAll();

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
//                'first_name',
//                'last_name',
//                'patronymic',
                'fullName' =>
                    [
                        'asc' =>  [ self::tableName().'.last_name' => SORT_ASC, self::tableName().'.first_name' => SORT_ASC, self::tableName().'.patronymic' => SORT_ASC],
                        'desc' => [ self::tableName().'.last_name' => SORT_DESC, self::tableName().'.first_name' => SORT_DESC, self::tableName().'.patronymic' => SORT_DESC],
                        'label' => Yii::t('app', 'Full name'),
                        'default' => SORT_ASC
                    ],
                'phoneDefault'=>
                    [
                        'asc' =>  [ 'phoneDef.phone_reference' => SORT_ASC,],
                        'desc' => [ 'phoneDef.phone_reference' => SORT_DESC,],
                        'label' => Yii::t('app', 'Phone'),
                        'default' => SORT_ASC
                    ],
                'gender',
                'email',
                'created_at',
                'updated_at',
                'created_by' =>
                    [
                        'asc'       => [ 'create.last_name' => SORT_ASC,  'create.first_name' => SORT_ASC, 'create.patronymic' => SORT_ASC  ],
                        'desc'      => [ 'create.last_name' => SORT_DESC, 'create.first_name' => SORT_DESC,'create.patronymic' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
                'updated_by' =>
                    [
                        'asc'       => [ 'update.last_name' => SORT_ASC,  'update.first_name' => SORT_ASC, 'update.patronymic' => SORT_ASC  ],
                        'desc'      => [ 'update.last_name' => SORT_DESC, 'update.first_name' => SORT_DESC,'update.patronymic' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
            ]
        ]);


        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query  ->andFilterWhere([
            Staff::tableName().'.id' => $this->id,
            Staff::tableName().'.gender'     => $this->gender,
            'DATE_FORMAT(FROM_UNIXTIME('.Staff::tableName().'.created_at),"%d-%m-%Y")' => $this->created_at,
            'DATE_FORMAT(FROM_UNIXTIME('.Staff::tableName().'.updated_at),"%d-%m-%Y")' => $this->updated_at,
        ]);

        $query  ->andFilterWhere(['like', 'email',                                  $this->email])
                ->andFilterWhere(['like', 'phoneDef.phone_reference',               $this->phoneDefault])   // Фильтровать по номеру телефона
                ->andFilterWhere(['like', 'create.last_name',                       $this->created_by])
                ->andFilterWhere(['like', 'update.last_name',                       $this->updated_by]);

        $names = explode(" ", $this->fullName);
        switch(count($names))
        {
            case 1 :
                $query
                        ->orFilterWhere(['like', self::tableName().'.last_name', $this->fullName])
                        ->orFilterWhere(['like', self::tableName().'.first_name', $this->fullName])
                        ->orFilterWhere(['like', self::tableName().'.patronymic', $this->fullName]);
                break;

            case 2 :

                //  Сортировка по Фамилии и Имени
                $query  ->andWhere(self::tableName().'.last_name LIKE "%' . $names[0] . '%" ' .
                                    'and '.self::tableName().'.first_name LIKE "%' . $names[1] . '%"'
                );

                //  Сортировка по Имени и Фамилии
                $query  ->orWhere(self::tableName().'.last_name LIKE "%' . $names[1] . '%" ' .
                                    'and '.self::tableName().'.first_name LIKE "%' . $names[0] . '%"'
                );

                //  Сортировка по Имени и Отчеству
                $query  ->orWhere(self::tableName().'.first_name LIKE "%' . $names[0] . '%" ' .
                                    'and '.self::tableName().'.patronymic LIKE "%' . $names[1] . '%"'
                );
                break;

            case 3 :
                $query
                        ->andFilterWhere(['like', self::tableName().'.last_name',  $names[0]])
                        ->andFilterWhere(['like', self::tableName().'.first_name', $names[1]])
                        ->andFilterWhere(['like', self::tableName().'.patronymic', $names[2]]);
                break;
        }

        return $dataProvider;
    }
}

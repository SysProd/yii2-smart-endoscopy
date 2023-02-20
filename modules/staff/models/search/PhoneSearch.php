<?php

namespace app\modules\staff\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

use app\modules\staff\models\Phone;

/**
 * PhoneSearch represents the model behind the search form about `app\modules\data\models\Phone`.
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
class PhoneSearch extends Phone
{
    const COUNT = 20; // количество элементов на одной странице

    public $id_from; // начало диапазона поиска по ID
    public $id_till; // конец диапазона поиска по ID
    public $created_at_from; // начало диапазона поиска по дате создания
    public $created_at_till; // конец диапазона поиска по дате создания
    public $updated_at_from; // начало диапазона поиска по дате обновления
    public $updated_at_till; // конец диапазона поиска по дате обновления

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_phone', 'default_phone'], 'integer'],

            [[ 'user_id', 'phone_reference', 'phone_template', 'comment', 'created_by', 'updated_by'], 'string', 'max' => 30],

            [['created_at', 'updated_at'], 'date', 'format'=>'dd-mm-yy'],

            [['type_phone'],    'in', 'range' => array_keys($this->typeList)],
            [['status_phone'],  'in', 'range' => array_keys($this->statusList)],
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
        $query = Phone::findByAll();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this::COUNT,
            ],
            // Сортировка по умолчанию
            'sort' => array(
                'defaultOrder' => ['phone_reference' => SORT_DESC],
            ),
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id_phone',
                'type_phone',
                'status_phone',
                'default_phone',
                'phone_reference',
                'created_by',
                'created_at',
                'updated_by',
                'updated_at',
                'comment',
                'user_id' =>
                    [
                        'asc' => ['fixedStaff.last_name' => SORT_ASC, 'fixedStaff.first_name' => SORT_ASC, 'fixedStaff.patronymic' => SORT_ASC],
                        'desc' => ['fixedStaff.last_name' => SORT_DESC, 'fixedStaff.first_name' => SORT_DESC, 'fixedStaff.patronymic' => SORT_DESC],
                        'label' => Yii::t('app', 'Full name'),
                        'default' => SORT_ASC
                    ],
                /*                'phoneDefault'=>
                                    [
                                        'asc' => ['phone.phone_reference' => SORT_ASC,],
                                        'desc' => ['phone.phone_reference' => SORT_DESC,],
                                        'label' => 'Телефон',
                                        'default' => SORT_ASC
                                    ],*/
            ]
        ]);


        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id_phone' => $this->id_phone,
            Phone::tableName().'.default_phone' => $this->default_phone,
            Phone::tableName().'.type_phone' => $this->type_phone,
            Phone::tableName().'.status_phone' => $this->status_phone,
            'DATE_FORMAT(FROM_UNIXTIME('.Phone::tableName().'.created_at),"%d-%m-%Y")' => $this->created_at,
            'DATE_FORMAT(FROM_UNIXTIME('.Phone::tableName().'.updated_at),"%d-%m-%Y")' => $this->updated_at,
        ]);

        $query
            ->andFilterWhere(['like', 'phone_reference',    $this->phone_reference])
            ->andFilterWhere(['like', 'phone_template',     $this->phone_template])
            ->andFilterWhere(['like', 'create.last_name',   $this->created_by])
            ->andFilterWhere(['like', 'update.last_name',   $this->updated_by])
            ->andFilterWhere(['like', 'comment',            $this->comment]);

        //  Фильтрация FullName по инициалам
        //  Превести строку поиска "fullName" в массив
        $names = explode(" ", $this->user_id);
        switch(count($names))
        {
            case 1 :
                $query
                    ->orFilterWhere(['like', 'fixedStaff.last_name',  $this->user_id])
                    ->orFilterWhere(['like', 'fixedStaff.first_name', $this->user_id])
                    ->orFilterWhere(['like', 'fixedStaff.patronymic', $this->user_id]);
                break;

            case 2 :

                //  Сортировка по Фамилии и Имени
                $query->andWhere('fixedStaff.last_name  LIKE "%' . $names[0] . '%" ' .
                             'and fixedStaff.first_name LIKE "%' . $names[1] . '%"'
                );

                //  Сортировка по Имени и Фамилии
                $query->orWhere('fixedStaff.last_name  LIKE "%' . $names[1] . '%" ' .
                            'and fixedStaff.first_name LIKE "%' . $names[0] . '%"'
                );

                //  Сортировка по Имени и Отчеству
                $query->orWhere('fixedStaff.first_name LIKE "%' . $names[0] . '%" ' .
                            'and fixedStaff.patronymic LIKE "%' . $names[1] . '%"'
                );
                break;

            case 3 :
                $query
                    ->andFilterWhere(['like', 'fixedStaff.last_name',  $names[0]])
                    ->andFilterWhere(['like', 'fixedStaff.first_name', $names[1]])
                    ->andFilterWhere(['like', 'fixedStaff.patronymic', $names[2]]);
                break;
        }

        return $dataProvider;
    }
}

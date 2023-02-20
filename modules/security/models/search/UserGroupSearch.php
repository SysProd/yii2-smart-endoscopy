<?php

namespace app\modules\security\models\search;

use app\modules\security\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\security\models\UserGroup;

/**
 * UserGroupSearch represents the model behind the search form about `app\modules\security\models\UserGroup`.
 */
class UserGroupSearch extends UserGroup
{
    const COUNT = 20; // количество элементов на одной странице

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'sort', 'avatar_img'], 'integer'],

            [['name', 'created_by', 'updated_by'], 'safe'],

            [['created_at', 'updated_at'], 'date', 'format'=>'dd-mm-yy'],
            [['status_system'], 'in', 'range' => array_keys($this->statusSystemList)],
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UserGroup::find()
            ->leftJoin( User::tableName() . ' as `create` on `create` .`id` = '.UserGroup::tableName().'.`created_by`')       // Создал
            ->leftJoin( User::tableName() . ' as `update` on `update` .`id` = '.UserGroup::tableName().'.`updated_by`');      // Обновил

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this::COUNT,
            ],
            // Сортировка по умолчанию
            'sort' => array(
                'defaultOrder' => ['name' => SORT_DESC],
            ),
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
             $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'sort' => $this->sort,
            'avatar_img' => $this->avatar_img,
            UserGroup::tableName().'.status_system' => $this->status_system,
            'DATE_FORMAT(FROM_UNIXTIME('.UserGroup::tableName().'.created_at),"%d-%m-%Y")' => $this->created_at,
            'DATE_FORMAT(FROM_UNIXTIME('.UserGroup::tableName().'.updated_at),"%d-%m-%Y")' => $this->updated_at,
        ]);

        $query ->andFilterWhere(['like', 'name', $this->name])
               ->andFilterWhere(['like', 'create.username', $this->created_by])
               ->andFilterWhere(['like', 'update.username', $this->updated_by]);

        return $dataProvider;
    }
}

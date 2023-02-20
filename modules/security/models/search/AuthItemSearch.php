<?php

namespace app\modules\security\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\security\models\AuthItem;
use app\modules\staff\models\Staff;
use app\modules\security\models\User;

/**
 * AuthItemSearch represents the model behind the search form about `app\modules\user\models\AuthItem`.
 */
class AuthItemSearch extends AuthItem
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'description', 'rule_name', 'data'], 'safe'],
            [['type',], 'in', 'range' => array_keys($this->typesList)],
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = AuthItem::find()
            ->leftJoin( User::tableName().' as `create`    on `create`   .`id`    = '.AuthItem::tableName().'.`created_by`')       // Создал
            ->leftJoin( User::tableName().' as `update`    on `update`   .`id`    = '.AuthItem::tableName().'.`updated_by`');      // Обновил

//      Запретить показывать роль "Root-Admin"
        if( !Yii::$app->user->can('Root-Admin') ) { $query->where("name != 'Root-Admin'"); }

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => 20
            ]
        ]);


        $this->load($params);

        if (!$this->validate()) {
            // Раскомментируйте следующую строку, если вы не хотите возвращать какие-либо записи при неудачной проверке
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'DATE_FORMAT(FROM_UNIXTIME('.AuthItem::tableName().'.created_at),"%d-%m-%Y")' => $this->created_at,
            'DATE_FORMAT(FROM_UNIXTIME('.AuthItem::tableName().'.updated_at),"%d-%m-%Y")' => $this->updated_at,
        ]);

        $query  ->andFilterWhere(['like', 'name',                       $this->name])
                ->andFilterWhere(['like', 'type',                       $this->type])
                ->andFilterWhere(['like', 'description',                $this->description])
                ->andFilterWhere(['like', 'rule_name',                  $this->rule_name])
                ->andFilterWhere(['like', 'data',                       $this->data])
                ->andFilterWhere(['like', 'create.last_name',           $this->created_by])
                ->andFilterWhere(['like', 'update.last_name',           $this->updated_by]);

        return $dataProvider;
    }
}

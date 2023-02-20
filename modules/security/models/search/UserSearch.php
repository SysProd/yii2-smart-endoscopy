<?php

namespace app\modules\security\models\search;

use Yii;
use yii\db\Query;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

use app\modules\security\models\User;
use app\modules\security\models\AuthItem;
use app\modules\security\models\UserProfile;
use app\modules\security\models\UserSession;

/**
 * UserSearch represents the model behind the search form about `app\modules\user\models\User`.

 * @property integer $id_from
 * @property integer $id_till
 * @property integer $created_at_from
 * @property integer $created_at_till
 * @property integer $updated_at_from
 * @property integer $updated_at_till
 *
 */
class UserSearch extends User
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
            [['id','id_from', 'id_till', ], 'integer'],
            [['email'], 'email'],

            [['username', 'confirmed_reg', 'roleName', 'created_at_from', 'created_at_till', 'updated_at_from', 'updated_at_till', 'created_by', 'updated_by'], 'string', 'max' => 30],

            [['created_at', 'updated_at'], 'date', 'format'=>'dd-mm-yy'],
            [['status_system'], 'in', 'range' => array_keys($this->statusSystemList)],
            [['onlineByUser'], 'in', 'range' => array_keys($this->onOffList)],
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

        return ArrayHelper::merge($label,$newLabel);
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
//        $this->scenario = User::SCENARIO_SEARCH_USER; // Устанавливаем сценарий поиска

        $query = User::find()
            ->joinWith(['roleNameArray'])                                                                                              // Подключение ролей Пользователей
//            ->leftJoin( UserProfile::tableName(). ' as `profile`   on `profile`  .`id`    = '.User::tableName().'.`id`')               // Профиль Пользователя
            ->leftJoin( User::tableName()       . ' as `confirmed` on `confirmed`.`id`    = '.User::tableName().'.`confirmed_reg`')    // Проверил
            ->leftJoin( User::tableName()       . ' as `create`    on `create`   .`id`    = '.User::tableName().'.`created_by`')       // Создал
            ->leftJoin( User::tableName()       . ' as `update`    on `update`   .`id`    = '.User::tableName().'.`updated_by`');      // Обновил

        // Скрыть "Пользователей" с ролью "Администраторы"
        // Показать "Пользователей" своей группы
        if( !(Yii::$app->user->can(AuthItem::ROLE_Admin) ) ) {
            $query  ->where  (['<>', AuthItem::tableName()  .'.name', AuthItem::ROLE_Admin])
                    ->orWhere([      AuthItem::tableName()  .'.name'     => null]);
        }

        //  Скрыть пользователей со статусом "Удаленные"
        if( !(Yii::$app->user->can('deleteUser')) ){ $query ->andFilterWhere (['<>', User::tableName().'.status_system', User::STATUS_SYSTEM_DELETED]); }

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this::COUNT,
            ],
            // Сортировка по умолчанию
            'sort' => array(
                'defaultOrder' => ['username' => SORT_DESC],
                ),
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'id',
                'username',
                'email',
                'status_system',
                'onlineByUser',
                'created_at',
                'updated_at',
                'confirmed_reg' =>
                    [
                        'asc'       => [ 'confirmed.username' => SORT_ASC ],
                        'desc'      => [ 'confirmed.username' => SORT_DESC ],
                        'default'   => SORT_ASC
                    ],
                'roleName' =>
                    [
                        'asc'  => [ AuthItem::tableName().'.name' => SORT_ASC ],
                        'desc' => [ AuthItem::tableName().'.name' => SORT_DESC ],
                        'default' => SORT_ASC
                    ],
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
            User::tableName().'.id' => $this->id,
            User::tableName().'.status_system' => $this->status_system,
            'DATE_FORMAT(FROM_UNIXTIME('.User::tableName().'.created_at),"%d-%m-%Y")' => $this->created_at,
            'DATE_FORMAT(FROM_UNIXTIME('.User::tableName().'.updated_at),"%d-%m-%Y")' => $this->updated_at,
        ]);

        $query  ->andFilterWhere(['like', User::tableName().'.username',$this->username])
                ->andFilterWhere(['like', User::tableName().'.email',   $this->email])
                ->andFilterWhere(['like', AuthItem::tableName().'.name',$this->roleName])
                ->andFilterWhere(['like', 'confirmed.username',         $this->confirmed_reg])
                ->andFilterWhere(['like', 'create.username',            $this->created_by])
                ->andFilterWhere(['like', 'update.username',            $this->updated_by]);

        if($this->onlineByUser){ // Сортировка пользователей по состоянию сессии Online|Offline
            // Заменить выбранный статус фильтрации на соответствующий оператор
            $onlineByUser = ($this->onlineByUser == self::STATUS_OFFLINE) ? 'NOT IN' : 'IN';
            // Получить массив Онлайн пользователей
            $listsBySession = !empty($listsBySession = $this->listsBySession) ? ArrayHelper::getColumn($listsBySession, 'user_id') : array();;
            // Применение фильтрации
            $query->andFilterWhere([$onlineByUser, self::tableName().'.id', $listsBySession]);
        }

        if ($this->id_from         && $this->id_till)         { $query->andFilterWhere(['between', User::tableName().'.id', $this->id_from, $this->id_till]); }
        if ($this->created_at_from && $this->created_at_till) { $query->andFilterWhere(['between', User::tableName().'.created_at', strtotime($this->created_at_from), strtotime($this->created_at_till)]); }
        if ($this->updated_at_from && $this->updated_at_till) { $query->andFilterWhere(['between', User::tableName().'.updated_at', strtotime($this->updated_at_from), strtotime($this->updated_at_till)]); }

        return $dataProvider;
    }
}

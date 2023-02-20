<?php

namespace app\modules\security\models;

use Yii;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;

use app\modules\security\models\User;
//use app\modules\security\models\User;

/**
 * This is the model class for table "auth_item".
 *
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property array $children
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_by
 * @property integer $updated_at
 * @property string $errorMessage
 *
 * @property AuthAssignment $authAssignment
 * @property User[] $user
 * @property AuthRule $ruleName
 * @property AuthItemChild[] $authItemChildren
 * @property AuthItem[] $children0
 * @property AuthItem[] $parents
 * @property AuthItem $types
 * @property AuthItem[] $typesList
 * @property User $createdBy
 * @property User $updatedBy
 */

class AuthItem extends ActiveRecord
{
    /**
     * Типы прав доступа к системе
     */
    const   TYPE_TASK       = '0',  // Задачи
            TYPE_ROLE       = '1',  // Роли
            TYPE_OPERATION  = '2';  // Операции

    public  $children       = [];  // Массив дочерних элементов

    private $errorMessage   = '';  // Ошибки запросов

    /**
     * Default "Роли" в системе
     */
    const   ROLE_Admin       = 'Администратор',
            ROLE_StaffAuthor = 'Сотрудник',
            ROLE_Device      = 'Устройство';

//            ROLE_UsersAuthor = 'Сотрудник',
//            ROLE_UsersGroup  = 'Устройство';

    /**
     * Default "Операции" в системе
     */
    const   OPR_CreateUser      = 'createUser',
            OPR_UpdateUser      = 'updateUser',
            OPR_UpdateProfile   = 'updateHisProfile',
            OPR_DeleteUser      = 'deleteUser',

            OPR_CreateContent = 'createContent',
            OPR_UpdateContent = 'updateContent',
            OPR_DeleteContent = 'deleteContent';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_item';
    }

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
//                'value' => time(),  // Атрибут типа даты
            ],
            [
                'class' => BlameableBehavior::className(),
                'attributes' =>
                    [
                        ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                        ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_by'],
                    ],
                'value' => empty( \Yii::$app->user->identity->id) ? NULL : \Yii::$app->user->identity->id,   // Изменить атрибут присвоения id
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type', 'description' ], 'required'],
            [['description', 'data'], 'string'],

            [['type'], 'in', 'range' => array_keys($this->typesList)],
            [['children'], 'each', 'rule' => ['string']],
//            [['children',], 'required', 'message' => 'Необходимо выбрать "Дочерние элементы"'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['name', 'rule_name'], 'string', 'max' => 64],

            [['rule_name'],         'exist', 'skipOnError' => true, 'targetClass' => AuthRule::className(),     'targetAttribute' => ['rule_name'       => 'name']],
            [['created_by'],        'exist', 'skipOnError' => true, 'targetClass' => User::className(),        'targetAttribute' => ['created_by'      => 'id']],
            [['updated_by'],        'exist', 'skipOnError' => true, 'targetClass' => User::className(),        'targetAttribute' => ['updated_by'      => 'id']],
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name'          => Yii::t('app', 'Name'),                   // 'Название',
            'type'          => Yii::t('app', 'type'),                   // 'Тип',
            'description'   => Yii::t('app', 'Description'),            // 'Описание',
            'rule_name'     => Yii::t('app', 'Role of element'),        // 'Роль элемента',
            'data'          => Yii::t('app', 'Serialize'),              // 'serialize - Генерируемое хранимое представление значения',            'created_at'    => \Yii::t('app', 'Created At'),
            'children'      => Yii::t('app', 'Children of elements'),   // 'Дочерние элементы',
            'created_at'    => Yii::t('app', 'Created At'),
            'created_by'    => Yii::t('app', 'Created By'),
            'updated_at'    => Yii::t('app', 'Updated At'),
            'updated_by'    => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignment()
    {
        return $this->hasOne(AuthAssignment::className(), ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('auth_assignment', ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleName()
    {
        return $this->hasOne(AuthRule::className(), ['name' => 'rule_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren()
    {
        return $this->hasMany(AuthItemChild::className(), ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren0()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'child'])->viaTable('auth_item_child', ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParents()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'parent'])->viaTable('auth_item_child', ['child' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * Создание "Прав доступа"
     * @return AuthItem
     */
    public function createItem()
    {
        $item = new AuthItem();
        $item->name = $this->name;
        $item->type = $this->type;
        $item->description = $this->description;
        $item->children = $this->children;
        $item->created_at = time();
//        $item->ruleName = trim($this->ruleName) ? trim($this->ruleName) : null;
//        Yii::$app->getAuthManager()->update($this->oldname, $item);
        $children = $item->authItemChildren;

        try {
            $item->save();
        } catch (\Exception $ex) {
            $this->errorMessage .= Yii::t('app', "Item <strong>{value}</strong> is not assigned:", ['value' => $item->name,]). " " . $ex->getMessage() . "<br />";
        }

        foreach ($this->children as $value) {
            try {
                $itemChild = new AuthItemChild();
                $itemChild->parent = $item->name;
                $itemChild->child = $value;
                $itemChild->save();
            } catch (\Exception $ex) {
                $this->errorMessage .= Yii::t('app', "Item <strong>{value}</strong> is not assigned:", ['value' => $value,]). " " . $ex->getMessage() . "<br />";
            }
        }

        return $item;
    }

    /**
     * Обновление "Прав доступа"
     * @return AuthItem
     */
    public function updateItem()
    {
        $item = new AuthItem();
        $item->name = $this->name;
        $item->type = $this->type;
        $item->description = $this->description;
//        $item->children = $this->children;
//        $item->ruleName = trim($this->ruleName) ? trim($this->ruleName) : null;
//        Yii::$app->getAuthManager()->update($this->oldname, $item);
        $children = $item->authItemChildren;

        /**
         * Удаление из массива страх элементов
         * Удаление из базы элементов отмеченых для удаления пользователем
         **/
        foreach ($children as $value) {
            $key = array_search($value->child, $this->children);
            if ($key === false) {
                AuthItemChild::deleteAll(['parent'=>$value->parent,'child'=>$value->child]);
            } else {
                unset($this->children[$key]);
            }
        }

        /**
         * Добавление новых отмеченых элементов пользователем в базу
         * Вывод ошибки при неудачном сохранении
         **/
        foreach ($this->children as $value) {
            try {
                $itemChild = new AuthItemChild();
                $itemChild->parent = $item->name;
                $itemChild->child = $value;
                $itemChild->save();

            } catch (\Exception $ex) {
                $this->errorMessage .= Yii::t('app', "Item <strong>{value}</strong> is not assigned:", [
                        'value' => $value,
                    ])
                    . " " . $ex->getMessage() . "<br />";
            }
        }

        return $item;

    }

    /**
     * Вывод ошибки запросов
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Список типов прав доступа к системе
     * @return array
     */
    public function getTypesList()
    {
        return [
            self::TYPE_OPERATION => \Yii::t('app', 'Operation'),
            self::TYPE_TASK      => \Yii::t('app', 'Task'),
            self::TYPE_ROLE      => \Yii::t('app', 'Role'),
        ];
    }

    /**
     * Список Ролей доступа к системе
     * @return array
     */
    public function getRoleList()
    {
        return [
            self::ROLE_Admin        => self::ROLE_Admin,
            self::ROLE_StaffAuthor  => self::ROLE_StaffAuthor,
            self::ROLE_Device       => self::ROLE_Device,
        ];
    }

    /**
     * Найти тип прав доступа из массива
     * @return AuthItem|string
     */
    public function getTypes()
    {
        $data = $this->typesList;
        return isset($data[$this->type]) ? $data[$this->type] : '---';
    }

}

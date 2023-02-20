<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 31.05.17
 * Time: 14:40
 */

namespace app\modules\security\models;

use Yii;

/**
 * This is the model class for table "user_session".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $ip
 * @property integer $expire
 * @property resource $data
 * @property integer $last_write
 */

class CustomDbSession extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_session}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ip'], 'required'],
            [['user_id', 'expire', 'last_write'], 'integer'],
            [['data'], 'string'],
            [['id'], 'string', 'max' => 80],
            [['ip'], 'string', 'max' => 15]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'            => \Yii::t('app', 'ID'),
            'user_id'       => \Yii::t('app', 'User ID'),    // ID - пользователя
            'ip'            => \Yii::t('app', 'Ip'),         // IP - адрес пк
            'expire'        => \Yii::t('app', 'Expire'),     // Время жизни сессий
            'data'          => \Yii::t('app', 'Data'),       // Данные сессий
            'last_write'    => \Yii::t('app', 'Last write'), // Последнее чтение сессий
        ];
    }
}
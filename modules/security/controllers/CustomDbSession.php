<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 31.05.17
 * Time: 14:37
 */

namespace app\modules\security\controllers;

use Yii;

use yii\db\Connection;
use yii\db\Query;
use yii\base\InvalidConfigException;
use yii\di\Instance;

class CustomDbSession extends \yii\web\DbSession
{
    public $writeCallback = ['\app\modules\security\controllers\CustomDbSession', 'writeCustomFields'];

    /**
     * @param $session
     * @return array
     */
    public function writeCustomFields($session) {

        try
        {
            $uid = (\Yii::$app->user->getIdentity(false) == null) ? null : \Yii::$app->user->getIdentity(false)->id;
            return ['user_id' => $uid, 'ip' => $_SERVER['REMOTE_ADDR'], 'last_write' => time()];
        }
        catch(\Exception $excp)
        {
            \Yii::info(print_r($excp), 'informazioni');
        }
    }
}
<?php

namespace app\modules\api;

use yii\filters\auth\HttpBasicAuth;
use yii\filters\AccessControl;

/**
 * ApiModules module definition class
 */
class ApiModules extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\api\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        \Yii::$app->user->enableSession = false; // Отключение сессии
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();


//        $behaviors['authenticator'] = [
//            'class' => HttpBasicAuth::className(),
//        ];

        return $behaviors;
    }
}

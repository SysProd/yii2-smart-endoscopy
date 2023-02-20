<?php

namespace app\modules\api\controllers;

//use yii\web\Controller;
use app\modules\endoscopy\models\CleaningLog;
use Codeception\Command\Clean;
use yii\rest\Controller;
use yii\rest\ActiveController;

/**
 * Default controller for the `ApiModules` module
 */
class CleaningLogController extends ActiveController
{
    public $modelClass = CleaningLog::class;

    /**
     * Renders the index view for the module
     * @return string
     */
/*    public function actionIndex()
    {
        return $this->render('index');
    }*/
}

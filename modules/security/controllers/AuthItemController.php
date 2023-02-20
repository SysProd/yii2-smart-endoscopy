<?php

namespace app\modules\security\controllers;

use Yii;

use app\models\Model;

use yii\web\Response;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;

use yii\bootstrap\ActiveForm;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

use kartik\icons\Icon;

use kartik\grid\EditableColumnAction;

use app\modules\security\models\User;
use app\modules\security\models\AuthItem;
use app\modules\security\models\search\AuthItemSearch;
use app\modules\security\models\AuthItemChild;


/**
 * AuthItemController implements the CRUD actions for AuthItem model.
 */
class AuthItemController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [AuthItem::ROLE_Admin],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete'            => ['POST'],
                    'remove-items'      => ['POST'],
                    'lists-auth-item'   => ['POST'],
                ],
            ],
        ];
    }

    public function actions()
    {
//        Edit actions for kartik\editable\Editable;
        return ArrayHelper::merge(parent::actions(), [
            'edit-description' => [                                // identifier for your editable action
                'class' => EditableColumnAction::className(),     // action class name
                'modelClass' => AuthItem::className(),            // the update model class
//                'outputValue' => function ($model) {
//                    $model->children = array(''=>'');   // исключить ошибку проверки массива
//                },
            ],
            'edit-type' => [
                'class' => EditableColumnAction::className(),
                'modelClass' => AuthItem::className(),
                'outputValue' => function ($model) {
                    /* @var $model \app\modules\security\models\AuthItem */
                    $value = $model->types;
//                    $model->children = array(''=>'');   // исключить ошибку проверки массива
                    return $value;
                },
            ]
        ]);
    }

    /**
     * Lists all AuthItem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AuthItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new AuthItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AuthItem();
        $items = self::ArrayList(null, AuthItem::TYPE_ROLE);

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post())) {

            if ($valid = $model->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {

                    $model->createItem();

                    if (strlen($model->getErrorMessage()) > 0) {
                        $transaction->rollBack();
                        throw new \Exception($model->getErrorMessage());   // Присвоение ошибки
                    }

                    \Yii::$app->session->setFlash('success', '<h4>' . Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Successful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'The data is saved') . '.</p>');
                    $transaction->commit();
                    return $this->redirect(['/security/auth-item/index']);

                } catch (\Exception $e) {
                    \Yii::$app->session->setFlash('error', '<h4>' . Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Unsuccessful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'When processing your request an error occurred.') . '.</p>');
                    $transaction->rollBack();
                    return $this->redirect(['/security/auth-item/index']);
                }

            }

        }
            return $this->renderAjax('_form', [
                'model' => $model,
                'children' => [],
                'items' => $items,
            ]);

    }

    /**
     * Updates an existing AuthItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {

            $children = isset(Yii::$app->request->post('AuthItem')['children']) ? Yii::$app->request->post('AuthItem')['children'] : null;
            $model->children = $children;

            if ($valid = $model->validate()) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {

                    if($children != null) {
                        $model->updateItem();
                        if (strlen($model->getErrorMessage()) > 0) {
                            $transaction->rollBack();
                            throw new \Exception($model->getErrorMessage());   // Присвоение ошибки
                        }
                    }

                    if($model->save()){
                        \Yii::$app->session->setFlash('success', '<h4>' . Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Successful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'The data is saved') . '.</p>');
                        $transaction->commit();
                        return $this->redirect(['/security/auth-item/index']);
                    }else{
                        $transaction->rollBack();
                        throw new \Exception(\Yii::t('app', 'Unsuccessful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'When processing your request an error occurred.'));   // Присвоение ошибки
                    }

                } catch (\Exception $e) {
                    \Yii::$app->session->setFlash('error', '<h4>' . Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Unsuccessful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'When processing your request an error occurred.') . '.</p>');
                    $transaction->rollBack();
                    return $this->redirect(['/security/auth-item/index']);
                }

            }
        }

           $items = self::ArrayList($model->name, $model->type);

            $children = $model->authItemChildren;
            $selected = [];

            foreach ($children as $child) {
                $selected[] = $child->child;
            }
            return $this->render('update', [
                'model'     => $model,
                'children'  => $selected,
                'items'     => $items,
            ]);

    }

    /**
     * Выборка массива элементов взависимости от типа элемента и его названия
     * @param string $name
     * @param string $type
     * @return array
     */
    private function ArrayList($name = null, $type)
    {
        /* @var $date \app\modules\security\models\AuthItem[] */
        /* @var $task \app\modules\security\models\AuthItem[] */
        switch ($type) {
            case AuthItem::TYPE_OPERATION:

                $date = AuthItem::find()
                    ->where(['type' => $type])
                    ->all();

//                  Удалить из масива элемент выведенный в update
                foreach ($date as $key => $list) {
                    if ($list->name == $name){
                        unset($date[$key]);
                    }
                }

                $items = ArrayHelper::map(
                    $date,
                    'name',
                    function ($date) {
                        /* @var $date \app\modules\security\models\AuthItem */
                        return $date->name.(strlen($date->description) > 0 ? ' ['.$date->description.']' : '');
                    },
                    function ($date) {
                        /* @var $date \app\modules\security\models\AuthItem */
                        return $date->types;
                    }
                );
                break;
            case AuthItem::TYPE_TASK:

                $task = AuthItem::find()
                    ->where(['type' => $type])
                    ->all();
                $operation = AuthItem::find()
                    ->where(['type' =>  AuthItem::TYPE_OPERATION])
                    ->all();

//                  Удалить из масива элемент выведенный в update
                foreach ($task as $key => $list) {
                    if ($list->name == $name){
                        unset($task[$key]);
                    }
                }
                $date = ArrayHelper::merge(
                    $operation,
                    $task
                );
                $items = ArrayHelper::map(
                    $date,
                    'name',
                    function ($date) {
                        /* @var $date \app\modules\security\models\AuthItem */
                        return $date->name.(strlen($date->description) > 0 ? ' ['.$date->description.']' : '');
                    },
                    function ($date) {
                        /* @var $date \app\modules\security\models\AuthItem */
                        return $date->types;
                    }
                );
                break;
            case AuthItem::TYPE_ROLE:

                $date = AuthItem::find()->all();
//              Запретить показывать роль "Root-Admin"
                if( !Yii::$app->user->can('Root-Admin') ) { $date = AuthItem::find()->where("name != 'Root-Admin'")->all(); }

//                  Удалить из масива элемент выведенный в update
                foreach ($date as $key => $list) {
                    if ($list->name == $name){
                        unset($date[$key]);
                    }
                }
                $items = ArrayHelper::map(
                    $date,
                    'name',
                    function ($date) {
                        /* @var $date \app\modules\security\models\AuthItem */
                        return $date->name.(strlen($date->description) > 0 ? ' ['.$date->description.']' : '');
                    },
                    function ($date) {
                        /* @var $date \app\modules\security\models\AuthItem */
                        return $date->types;
                    }
                );
                break;
            default:
                throw new \InvalidArgumentException( \Yii::t('app', 'Invalid element type.') );
        }
                return $items;
    }

    /**
     * Список дочерних элементов исходя из выбора пользователя
     * @return string options for selected
     * list AuthItem
     */
    public function actionListsAuthItem()
    {

        $name = Yii::$app->request->post('name');
        $type = Yii::$app->request->post('type');

        $items = self::ArrayList($name, $type);

        if(count($items) > 0)
        {
            echo "<option value='-1'>". \Yii::t('app', 'Choose items') ."</option>";

            foreach($items as $key => $type){
                echo "<optgroup label=" . $key . ">";
                foreach($type as $keyType => $name){
                    echo "<option value='".$keyType."'>".$name."</option>";
                }
                echo "</optgroup>";
            }
        }else{
            echo "<option value=''></option> <option> - </option>";
        }
    }

    /**
     * Удаление выбранных элементов
     * @return bool
     * @throws \Exception
     */
    public function actionRemoveItems()
    {

        $items = Yii::$app->request->post('items');

        if ( ($authItems = AuthItem::findAll($items)) !== null && !empty($items) ){

            $array = array();   // Массив удачный запросов
            $error = array();   // Массив ошибок

            // Пребор массива с элементами для удаления
            foreach ($authItems as $item) {
                if ($item->delete() or AuthItemChild::deleteAll(['parent' => $item->name])){
                    $array[] = $item->name;
                } else {
                    $error[] = $item->name;
                }
            }

            // Проверить рассхождение массивов и добавить их к массиву ошибок
            // Перевести массив ошибок в строку
            $error = implode(",", array_merge(array_diff($items,$array),$error));
            // Перевести массив удачных запросов в строку
            $array = implode(",", $array);

            // Вывод уведомлений
            if(!empty($array) && empty($error)){
                Yii::$app->session->setFlash('success', '<h4>'.Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']).Yii::t('app','Successful').  '</h4><hr class="kv-alert-separator" /><p> '.Yii::t('app','{nameType} <strong>{fullName}</strong> removed from the system',    ['nameType' => \Yii::t('app', 'Access rights'), 'fullName' => Html::encode($array)]).'.</p>');
            }else if(!empty($array) && !empty($error)){
                Yii::$app->session->setFlash('error',   '<h4>'.Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']).Yii::t('app','Unsuccessful').'</h4><hr class="kv-alert-separator" /><p> '.Yii::t('app','{nameType} <strong>{fullName}</strong> not removed from the system',['nameType' => \Yii::t('app', 'Access rights'), 'fullName' => Html::encode($error)]).'.</p>');
                Yii::$app->session->setFlash('success', '<h4>'.Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']).Yii::t('app','Successful').  '</h4><hr class="kv-alert-separator" /><p> '.Yii::t('app','{nameType} <strong>{fullName}</strong> removed from the system',    ['nameType' => \Yii::t('app', 'Access rights'), 'fullName' => Html::encode($array)]).'.</p>');
            }else if( empty($array) && !empty($error)){
                Yii::$app->session->setFlash('error',   '<h4>'.Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']).Yii::t('app','Unsuccessful').'</h4><hr class="kv-alert-separator" /><p> '.Yii::t('app','{nameType} <strong>{fullName}</strong> not removed from the system',['nameType' => \Yii::t('app', 'Access rights'), 'fullName' => Html::encode($error)]).'.</p>');
            }

        }else{
            \Yii::$app->session->setFlash('error', '<h4>' . Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']) . \Yii::t('app', 'Unsuccessful') . '</h4><hr class="kv-alert-separator" /><p> ' . \Yii::t('app', 'Failed to remove «{attribute}»', ['attribute' => \Yii::t('app', 'Access rights')]) . '.</p>');
        }

        return $this->redirect(['index']);

    }

    /**
     * Deletes an existing AuthItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $authItem = $this->findModel($id);
        $authName = $authItem->name;

        if ($authItem->delete()) {
            Yii::$app->session->setFlash('success', '<h4>'.Icon::show('check-circle', ['class' => 'fa-lg kv-alert-title']).Yii::t('app','Successful').  '</h4><hr class="kv-alert-separator" /><p> '.Yii::t('app','{nameType} <strong>{fullName}</strong> removed from the system',    ['nameType' => \Yii::t('app', 'Access rights'), 'fullName' => Html::encode($authName)]).'.</p>');
        } else {
            Yii::$app->session->setFlash('error',   '<h4>'.Icon::show('times-circle', ['class' => 'fa-lg kv-alert-title']).Yii::t('app','Unsuccessful').'</h4><hr class="kv-alert-separator" /><p> '.Yii::t('app','{nameType} <strong>{fullName}</strong> not removed from the system',['nameType' => \Yii::t('app', 'Access rights'), 'fullName' => Html::encode($authName)]).'.</p>');
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AuthItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(\Yii::t('app', 'Page not found.'));
        }
    }
}
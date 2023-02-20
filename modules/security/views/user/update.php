<?php

use yii\helpers\Html;
use kartik\widgets\AlertBlock;
use kartik\icons\Icon;
use kartik\alert\Alert;
use igor162\adminlte\widgets\Tabs;

/* @var $this yii\web\View */
/* @var $model app\modules\security\models\User */
/* @var $profile app\modules\security\models\UserProfile */
/* @var array $items */
/* @var array $role */

$this->title = \Yii::t('app', 'Change data of «{attribute}» #{item}', ['attribute' => \Yii::t('app', 'user'), 'item' => $model->id]);
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'User List'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Change data of «{attribute}» #{item}', ['attribute' => \Yii::t('app', 'user'), 'item' => $model->id]) ];
?>
<div class="users-update pull-left">

    <?php
    echo AlertBlock::widget([
        'useSessionFlash' => true,
        'type' => AlertBlock::TYPE_GROWL,
    ]);
    ?>
            <?php
            $items = [
                [
                    'label' => Icon::show('lock', ['class' => 'fa-lg']).\Yii::t('app', 'Authentication data'),
                    'url' => ['update', 'id' => $model->id, 'tab' => 'auth', ],
                    'content'=>
                        $this->render('_form', [
                            'model' => $model,
                            'items' => $items,
                            'role' => $role,
                        ]),
                    'active' => (Yii::$app->request->get('tab') == 'auth') ? true : false,
                ],
                [
                    'label' => Icon::show('user-secret', ['class' => 'fa-lg']).\Yii::t('app', 'Profile'),
                    'url' => ['update', 'id' => $model->id, 'tab' => 'profile', ],
                    'content'=>
                        $profile == null ?
                            Alert::widget([
                                'type' => Alert::TYPE_DANGER,
                                'title' => \Yii::t('app','Error'),
                                'icon' => 'glyphicon glyphicon-remove-sign',
                                'body' => \Yii::t('app', 'The user profile is not created. <br /> To fix you need to click on the link') .' '. Html::a( Icon::show('user-plus', ['class' => 'fa-lg']), ['/staff/staff/create', 'id' => $model->id], ['class' => 'btn btn-primary', 'title' => \Yii::t('app', 'Create a profile')]),
                                'showSeparator' => true,
                            ])
                             :
                $this->render('_profile', [
                        'profile' => $profile,
                    ]),
                    'active' => (Yii::$app->request->get('tab') == 'profile') ? true : false,
                ],
            ];

            // Above
            echo Tabs::widget([
                'items'=>$items,
                'encodeLabels'=>false
            ]);
            ?>
</div>

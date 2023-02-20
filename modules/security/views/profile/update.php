<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use kartik\widgets\AlertBlock;
use kartik\icons\Icon;
use kartik\alert\Alert;
use igor162\adminlte\widgets\Tabs;

/* @var $this yii\web\View */
/* @var $model app\modules\security\models\User */
/* @var $profile app\modules\security\models\UserProfile */
/* @var $resetPass app\modules\security\models\ResetPasswordAdmin */

$this->title = \Yii::t('app', 'Change data of «{attribute}»', ['attribute' => \Yii::t('app', 'my profile')]);
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'My profile') ];
?>
<div class="users-update pull-left">

    <?php
    Modal::begin([
        'size' => Modal::SIZE_LARGE,
        'header' => '<h4 class="text-left" style="color: #000; font-size: 20px; font-weight: 500;"></h4>',
        'closeButton' => false,
        'toggleButton' => false,
        'options' => [
            'id' => 'modal-profile',
            'tabindex' => false // important for Select2 to work properly
        ],
    ]);
    echo "<div id='modalContent-profile'> </div>";
    Modal::end();

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
                [
                    'label' => Html::img('/images/reset-password-24.svg', ['class' => '']).' '.\Yii::t('app', 'Reset password'),
                    'url' => ['update', 'id' => $model->id, 'tab' => 'reset', ],
                    'content'=>
                        $this->render('resetPass', [
                            'model' => $resetPass,
                        ]),
                    'active' => (Yii::$app->request->get('tab') == 'reset') ? true : false,
                ],
                [
                    'label' => Icon::show('times-rectangle-o', ['class' => 'btn btn-danger btn-xs']),
                    'headerOptions' => ['class' => 'pull-right'],
                    'linkOptions' => [
//                        'class' => 'btn btn-primary btn-xs',
                        'title' => \Yii::t('app', 'Account deleting'),
                        'data' => [
                            'data-skin' => "skin-red",
                            'confirm' =>  \Yii::t('app', 'Are you sure you want to delete account deleting?'),
                            'method' => 'post',
                        ],
                    ],
                    'url' => ['delete', 'id' => $model->id],
                ],
            ];

            // Above
            echo Tabs::widget([
                'items'=>$items,
                'encodeLabels'=>false
            ]);
            ?>
</div>

<?php

use yii\helpers\Html;

use kartik\widgets\AlertBlock;

use kartik\icons\Icon;

use kartik\grid\GridView;
use kartik\grid\BooleanColumn;

use app\modules\data\models\Phone;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\data\models\search\PhoneSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = \Yii::t('app', 'List of phone');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="phone-index">

    <?php
    echo AlertBlock::widget([
        'useSessionFlash' => true,
        'type' => AlertBlock::TYPE_GROWL,
    ]);
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'export' => false,
        'hover'=>true,
        'striped'=>false,
        'rowOptions' => function($model){
            /* @var $model app\modules\data\models\Phone */
            if      ($model->status_system == Phone::STATUS_SYSTEM_IRRELEVANT){
                return ['class' => 'notActual-select'];
            }elseif ($model->status_system == Phone::STATUS_SYSTEM_BLOCKED){
                return ['class' => 'blocked-select'];
            }elseif ($model->status_system == Phone::STATUS_SYSTEM_DELETED){
                return ['class' => 'remove-select'];
            }
        },
        'columns' => [
//            ['class' => 'yii\grid\SerialColumn'],

//            'id_phone',
            [
                'attribute'=>'phone_reference',
                'options' => ['width' => '18%'],
                'format' => 'html',
//                'filterWidgetOptions'=>
//                    [
//                         setting filter widget
//                        'mask' => '+7(999) 999-99-99',
//                        'options' => ['placeholder' => $searchModel->getAttributeLabel("phone_reference").' ...'],
//                        'clientOptions' => [ 'removeMaskOnSubmit' => true, ]
//                    ],
//                'filterType'=>'\yii\widgets\MaskedInput',
//                'filter' => false,
                'value'=>function ($model) {
                    /* @var $model app\modules\data\models\Phone */
                    return !isset($model->phone_reference) ? null :  Html::a(Yii::$app->formatter->asPhoneFormatter($model->phone_reference), [ "update", "id" => $model->id_phone], [ 'title' => \Yii::t('app', 'Change the phone') ]);
                },
            ],
            [
                'attribute' => 'user_id',
                'options' => ['width' => '20%'],
                'value' => function ($model){
                    /* @var $model app\modules\data\models\Phone */
                    return !isset($model->user_id) ? null : $model->staff->fullName;
                }
            ],
            [
                'attribute' => 'counterparty_id',
                'options' => ['width' => '20%'],
                'value' => function ($model){
                    /* @var $model app\modules\data\models\Phone */
                    return !isset($model->counterparty_id) ? null : $model->counterparty->short_name ;
                }
            ],
//            'phone_reference',
//            'user_id',
//            'counterparty_id',
            [
                'attribute'=>'type_phone',
                'options' => ['width' => '12%'],
            'format'=>'html',
                'filterWidgetOptions'=>
                    [
                        // setting filter widget
                        'data' => $searchModel->typeList,
                        'options' => ['placeholder' => ' ...'],
                        'hideSearch' => true,
                        'pluginOptions' =>
                            [
                                'allowClear' => true,
                                'autoUpdateInput' => false,
                            ]
                    ],
                'filterType'=>GridView::FILTER_SELECT2,     // Фильтр для таблицы
                'value'=>function ($model)
                {
                    /* @var $model app\modules\data\models\Phone */
                    return $model->typeStyle;
                },
            ],
            [
                'attribute'=>'default_phone',
                'options' => ['width' => '12%'],
                'format'=>'html',
                'filterWidgetOptions'=>
                    [
                        // setting filter widget
                        'data' => $searchModel->defaultPhoneList,
                        'options' => ['placeholder' => ' ...'],
                        'hideSearch' => true,
                        'pluginOptions' =>
                            [
                                'allowClear' => true,
                                'autoUpdateInput' => false,
                            ]
                    ],
                'filterType'=>GridView::FILTER_SELECT2,     // Фильтр для таблицы
                'value'=>function ($model)
                {
                    /* @var $model app\modules\data\models\Phone */
                    return $model->defaultPhoneStyle;
                },
            ],
            [
                'attribute'=>'status_phone',
                'options' => ['width' => '11%'],
                'format' => 'html',
                'filterWidgetOptions'=>
                    [
                        // setting filter widget
                        'data' => $searchModel->statusList,
                        'options' => ['placeholder' => ' ...'],
                        'hideSearch' => true,
                        'pluginOptions' =>
                            [
                                'allowClear' => true,
                                'autoUpdateInput' => false,
                            ]
                    ],
                'filterType'=>GridView::FILTER_SELECT2,     // Фильтр для таблицы
                'value'=>function ($model)
                {
                    /* @var $model app\modules\data\models\Phone */
                    return $model->statusStyle;
                },
            ],
//            'type_phone',
//            'status_phone',
//             'default_phone',
            // 'phone_template',
            // 'created_at',
            // 'updated_at',
            // 'comment',
/*            [
                'attribute'=>'status_system',
                'options' => ['width' => '78'],
                'format' => 'html',
                'filterWidgetOptions'=>
                    [    // setting filter widget
                        'data' =>  $searchModel->statusSystemList,
                        'options' => ['placeholder' => ' ...'],
                        'hideSearch' => true,
                        'pluginOptions' =>
                            [
                                'allowClear' => true,
                                'autoUpdateInput' => false,
                            ]
                    ],
                'filterType'=>GridView::FILTER_SELECT2,     // Фильтр для таблицы
                'value'=>function ($model) { return $model->statusSystemStyle; },
            ],*/
            [
                'class' => 'yii\grid\ActionColumn',
                'options' => ['width' => '1%'],
                'template' => '{delete}',
                'buttons'=>[
                    'delete'=>function($url, $model){
                        /* @var $model app\modules\data\models\Phone */

                        if(Yii::$app->user->can('staff-absolute_delete')){
                            return Html::a(Icon::show('trash', ['class' => 'fa-lg']), ['delete', 'id' => $model->id_phone], [
                                'data' => [
                                    'confirm' =>  \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    'method' => 'post',
                                ],
                                'title' => \Yii::t('app', 'Complete delete of «{attribute}» #{item}', ['attribute' => \Yii::t('app', 'phone'), 'item' => \Yii::$app->formatter->asPhoneFormatter($model->phone_reference)]),
                            ]);
                        }elseif(Yii::$app->user->can('phones-delete')){
                            return Html::a(Icon::show('trash', ['class' => 'fa-lg']), ['delete', 'id' => $model->id_phone], [
                                'data' => [
                                    'confirm' => \Yii::t('app', 'Are you sure you want to delete this item?'),
                                    'method' => 'post',
                                ],
                                'title' => \Yii::t('app', 'Delete of «{attribute}» #{item}', ['attribute' => \Yii::t('app', 'Phone'), 'item' => \Yii::$app->formatter->asPhoneFormatter($model->phone_reference)]),
                            ]);
                        }else{
                            return '';
                        }
                    },
                ]
            ],
        ],
        'toolbar' => [
//            Html::a('<i class="glyphicon glyphicon-plus"></i>', ['create'], ['class' => 'btn btn-success', 'title' => 'Добавить телефон']) . ' ' .
            Html::a(Icon::show('repeat', ['class' => 'fa-lg']), ['index'], [
                'class' => 'btn btn-default',
                'title' => \Yii::t('app', 'Update'),
            ]),
            '{toggleData}',
        ],
        'panel'=>[
            'type'=>GridView::TYPE_SUCCESS,
            'heading'=>Icon::show('phone-square', ['class' => 'fa-lg']).Html::encode($this->title),
            'afterOptions' => false,
        ],
    ]); ?>
</div>

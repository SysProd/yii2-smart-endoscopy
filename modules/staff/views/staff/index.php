<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\staff\models\search\StaffSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

use yii\bootstrap\Modal;

use igor162\grid\GridView;
use igor162\dynagrid\DynaGrid;
use igor162\RemoveButton\RemoveModal;
use igor162\RemoveButton\RemoveAllButton;

use kartik\widgets\AlertBlock;
use kartik\icons\Icon;
use kartik\editable\Editable;
use kartik\depdrop\DepDrop;

use app\widgets\actions\Helper;
use app\modules\staff\models\Staff;

$this->title = \Yii::t('app', 'List of staff');
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Personnel records')];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= RemoveModal::widget([
    'bodyTittle' => 'Are you sure you want to delete this item?'
]); ?>

<?= AlertBlock::widget([
    'useSessionFlash' => true,
    'type' => AlertBlock::TYPE_GROWL,
]);
?>

<?php
Modal::begin([
    'size' => Modal::SIZE_LARGE,
    'header' => '<h4 class="text-left" style="color: #000; font-size: 20px; font-weight: 500;"></h4>',
//    'closeButton' => false,
//    'toggleButton' => false,
    'options' => [
        'id' => 'modal-staff',
        'tabindex' => false, // important for Select2 to work properly
        'data-backdrop' => "static", // Запретить закрытие модального окна при нажатии за фоном
    ],
]);
Modal::end();
?>

<div class="staff-index">

    <?
    $columns = [

        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute'=>'fullName',
            'options' => ['width' => '300'],
            'format'  => 'html',
            'value'=>function ($model) {
                /* @var $model app\modules\staff\models\Staff */
                return  Yii::$app->user->can('staff-totals-update')
                    ? Html::a(Icon::show('pencil-square', ['class' => 'fa-lg']).$model->fullName, [ "/staff/staff/update", "id" => $model->id, 'returnUrl' => Helper::getReturnUrl()], [ 'title' => \Yii::t('app', 'Change data of «{attribute}» #{item}', ['attribute' => \Yii::t('app', 'employee'), 'item' => $model->id]) ])
                    : $model->fullName;
            }
        ],
        [
            'attribute'=>'gender',
//                'options' => ['width' => '10'],
            'filterWidgetOptions'=>
                [
                    // setting filter widget
                    'data' => $searchModel->genderList,
                    'options' => ['placeholder' => ' ...'],
                    'hideSearch' => true,
                    'pluginOptions' =>
                        [
                            'allowClear' => true,
                            'autoUpdateInput' => false,
                        ]
                ],
            'filterType'=>GridView::FILTER_SELECT2,     // Фильтр для таблицы
            'value'=>function ($model) {
                /* @var $model app\modules\staff\models\Staff */
                return !isset($model->gender) ? null : $model->gender0;
            },
        ],
        [
            'attribute'=>'email',
//                'options' => ['width' => '15%'],
            'format'=>'email'
        ],
        [
            'attribute'=>'phoneDefault',
            'options' => ['width' => '150'],
//                'filter' => false,
            'value'=>function ($model) {
                /* @var $model app\modules\staff\models\Staff */
                return !isset($model->phoneDefault) ? null : Yii::$app->formatter->asPhoneFormatter($model->phoneDefault->phone_reference);
            },
        ],
        [
            'attribute'=>'created_at',
            'format'=>'datetime',
            'filterWidgetOptions'=>
                [
                    'model' => $searchModel,
                    'attribute' => 'created_at',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ],
            'filterType'=>GridView::FILTER_DATE,     // Фильтр для таблицы
            'visible' => \Yii::$app->user->can('staff-show_createdAt'),
        ],
        [
            'attribute'=>'created_by',
            'value'=>function ($model) {
                /* @var $model app\modules\staff\models\Staff */
                return empty($model->created_by) ? null :$model->createdBy->shortName;
            },
            'visible' => \Yii::$app->user->can('staff-show_createdBy'),
        ],
        [
            'attribute'=>'updated_at',
            'format'=>'datetime',
            'filterWidgetOptions'=>
                [
                    'model' => $searchModel,
                    'attribute' => 'updated_at',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ],
            'filterType'=>GridView::FILTER_DATE,     // Фильтр для таблицы
            'visible' => \Yii::$app->user->can('staff-show_updatedAt'),
        ],
        [
            'attribute'=>'updated_by',
            'value'=>function ($model) {
                /* @var $model app\modules\staff\models\Staff */
                return empty($model->updated_by) ? null :$model->updatedBy->shortName;
            },
            'visible' => \Yii::$app->user->can('staff-show_updatedBy'),
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'options' => ['width' => '60'],
            'template' => '{update} {reg} {reg_update} {delete}',
            'buttons'=>[
                'reg' => function ($url, $model, $key){
                    /* @var $model app\modules\staff\models\Staff */
                    if(!Yii::$app->user->can('createStaff')){
                        return null;
                    }

                    if( empty($model::checkRegStaff($model->id))) {
                        return Html::a(
                            Icon::show('user-plus', ['class' => 'fa-lg']),
                            ['/security/user/reg', 'id' => $model->id, 'form' => $model::FORM_TYPE_AJAX, 'returnUrl' => Helper::getReturnUrl()],
                            [
                                'class' => 'activity-view-link',
                                'title' => \Yii::t('app', 'Register {attribute} #{item}',['attribute'=>\Yii::t('app', 'employee'), 'item'=>$model->id,]),
                                'data-toggle' => 'modal',
                                'data-target' => '#modal-staff',
                                'data-id' => $key,
                                'data-pjax' => '0',
                            ]   );
                    }
                },

                'update' => function ($url, $model, $key) {
                    /* @var $model app\modules\staff\models\Staff */
                    return Html::a(Icon::show('pencil', ['class' => 'fa-lg']), ['/staff/staff/update', 'id' => $model->id, 'returnUrl' => Helper::getReturnUrl()], [
                        'title' => \Yii::t('app', 'Change data of «{attribute}» #{item}', ['attribute' => \Yii::t('app', 'stafF'), 'item' => $model->id]),
                    ]);
                },
                'delete'=>function($url, $model){
                    /**
                     * @var $model app\modules\staff\models\Staff
                     * Разрешить "Удаление" сотрудников для пользователей с операцией "deleteStaff"
                     */
                    if((Yii::$app->user->can('deleteStaff') && !((isset($model->user) && $model->user->rootRole)))){
                        return Html::a(Icon::show('trash-o', ['class' => 'fa-lg']), ['delete', 'id' => $model->id], [
                            'data' => [
                                'confirm' =>  \Yii::t('yii', 'Are you sure you want to delete this item?'),
                                'method' => 'post',
                            ],
                            'title' => \Yii::t('app', 'Complete delete of «{attribute}» #{item}', ['attribute' => \Yii::t('app', 'employee'), 'item' => $model->fullName]),
                        ]);
                    }else{
                        return null;
                    }
                },
            ]
        ],
    ];

    /** Кнопка добавления */
    /** @var $button */
    $button = Html::button(Icon::show('plus-circle'), [
        'value' => Url::toRoute(['update', 'form' => Staff::FORM_TYPE_AJAX, 'returnUrl' => Helper::getReturnUrl()]),
        'class' => 'btn btn-' . GridView::TYPE_WARNING . ' btn-sm',
        'title' => \Yii::t('app', 'Add «{attribute}»', ['attribute' => Yii::t('app', 'employee')]),
        'onclick' =>
            '   $("#modal-staff").modal("show")
                                        .find(".modal-header h4").text("' . \Yii::t('app', 'Adding data of «{attribute}»', ['attribute' => Yii::t('app', 'employee')]) . '")
                                        .end()
                                        .find(".modal-body")
                                        .load($(this).attr("value"));
                                            ',
    ]);

    echo DynaGrid::widget([
        'columns' => $columns,
        'storage' => DynaGrid::TYPE_DB,
        'showPersonalize' => true,
        'allowThemeSetting' => false,
        'allowFilterSetting' => false,
        'allowSortSetting' => false,
        'gridOptions' => [
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'pjax' => true,
            'export' => false,
            'hover' => true,
            'striped' => false,
            'panel' => [
                'type' => GridView::TYPE_INFO,
                'heading' => Icon::show('users', ['class' => 'fa-lg']).Html::encode($this->title),
                'after' => false,
                'before' => false,
            ],
            'toolbar' => [
                ['content' =>
                    $button .
                    ' ' . Html::button(Icon::show('search'), [
                        'id' => 'ButtonSearch',
                        'value' => Url::toRoute(['search']),
                        'class' => 'btn btn-' . GridView::TYPE_PRIMARY . ' btn-sm',
                        'title' => \Yii::t('app', 'Search by parameters'),
                        'onclick' =>
                            '   $("#modal-staff").modal("show")
                                                        .find(".modal-header h4").text("' . \Yii::t('app', 'Search by parameters') . '").end()
                                                        .find(".modal-dialog").removeClass().addClass("modal-dialog ' . Modal::SIZE_LARGE . '").end()
                                                        .find("#modalContent-staff")
                                                        .load($(this).attr("value"));
                                                            ',
                    ]) . ' ' .
                    Html::a(Icon::show('repeat'), ['index'], [
                        'class' => 'btn btn-default btn-sm',
                        'id' => 'refreshButton',
                        'title' => \Yii::t('app', 'Update'),
                    ])
                ],
//                \Yii::$app->user->can(AuthItem::ROLE_Admin) ? ['content' => '{dynagrid}'] : '',
//                        '{export}',
                ['content' => '{dynagrid}'],
                '{toggleData}',
            ]
        ],
        'options' => ['id' => 'dynagrid-'.$searchModel->formName()] // уникальный идентификатор для настройки таблицы
    ]);
    ?>

</div>

<?php

$titleNameUpdate = \Yii::t('app', 'Register «{attribute}» in system', ['attribute' => Yii::t('app', 'user')]);
//$titleNameUpdate = \Yii::t('app', 'Editing of «{attribute}»', ['attribute' => Yii::t('app', 'Cleaning Agent')]);

$script = <<< JS

$(".activity-view-link").click(function() {
    $.get(
        $(this).attr("href"),
        {
            id: $(this).closest("tr").data("key"),
        },
        function (data) {
            $("#modal-staff").modal("show")
             .find(".modal-header h4").text("{$titleNameUpdate}").end()
             .find('.modal-body').html(data);
        }
    );
});

JS;

$this->registerJs($script);
?>
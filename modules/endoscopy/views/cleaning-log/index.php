<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;

use igor162\RemoveButton\RemoveModal;
use igor162\grid\GridView;
use igor162\dynagrid\DynaGrid;
use igor162\adminlte\ColorCSS;

use kartik\icons\Icon;
use kartik\widgets\AlertBlock;


use app\widgets\actions\Helper;
use app\modules\endoscopy\models\CleaningLog;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\endoscopy\models\search\CleaningLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Cleaning Logs');
$this->params['breadcrumbs'][] = $this->title;
?>
<?= RemoveModal::widget([
    'bodyTittle' => 'Are you sure you want to delete this item?'
]); ?>
<?php
Modal::begin([
    'size' => Modal::SIZE_LARGE,
    'header' => '<h4 class="text-left" style="color: #000; font-size: 20px; font-weight: 500;"></h4>',
//    'closeButton' => false,
//    'toggleButton' => false,
    'options' => [
        'id' => 'modal-cleaning-log',
        'tabindex' => false, // important for Select2 to work properly
        'data-backdrop' => "static", // Запретить закрытие модального окна при нажатии за фоном
    ],
]);
Modal::end();
?>
<?= AlertBlock::widget([
    'useSessionFlash' => true,
    'type' => AlertBlock::TYPE_GROWL,
]);
?>
<div class="cleaning-log-index">

    <?
    $columns = [
        ['class' => 'yii\grid\SerialColumn'],
//        'id',
        [
            'attribute'=>'staff_by',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLog */
                return empty($model->staff_by) ? null : $model->staffBy->last_name;
            },
        ],
        [
            'attribute'=>'add_data',
            'format'=>'datetime',
            'filterWidgetOptions'=>
                [
                    'model' => $searchModel,
                    'attribute' => 'add_data',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ],
            'filterType'=>GridView::FILTER_DATE,     // Фильтр для таблицы
        ],
        [
            'attribute'=>'tools_by',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLog */
                return empty($model->tools_by) ? null : $model->toolsBy->fxTools->name;
            },
        ],
        [
            'attribute'=>'test_tightness_by',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLog */
                return empty($model->test_tightness_by) ? null : $model->testTightnessBy->fxToolsStatuses->name;
            },
        ],
        [
            'attribute'=>'cleaning_agents_by',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLog */
                return empty($model->cleaning_agents_by) ? null : $model->cleaningAgentsBy->fxToolsAgents->name;
            },
        ],
        [
            'attribute'=>'cleaning_start',
            'format'=>'datetime',
            'filterWidgetOptions'=>
                [
                    'model' => $searchModel,
                    'attribute' => 'cleaning_start',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ],
            'filterType'=>GridView::FILTER_DATE,     // Фильтр для таблицы
        ],
        [
            'attribute'=>'cleaning_end',
            'format'=>'datetime',
            'filterWidgetOptions'=>
                [
                    'model' => $searchModel,
                    'attribute' => 'cleaning_end',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ],
            'filterType'=>GridView::FILTER_DATE,     // Фильтр для таблицы
        ],
        [
            'attribute'=>'test_qualities_cleaning_status',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLog */
                return empty($model->test_qualities_cleaning_status) ? null : $model->testQualitiesCleaningStatus->fxToolsStatuses->name;
            },
        ],
        [
            'attribute'=>'test_qualities_cleaning_date',
            'format'=>'datetime',
            'filterWidgetOptions'=>
                [
                    'model' => $searchModel,
                    'attribute' => 'test_qualities_cleaning_date',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ],
            'filterType'=>GridView::FILTER_DATE,     // Фильтр для таблицы
        ],
        [
            'attribute'=>'disinfection_type_by',
            'format'=>'html',
            'filterWidgetOptions'=>
                [
                    // setting filter widget
                    'data' => $searchModel->disinfectionTypeList,
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
                /* @var $model app\modules\endoscopy\models\CleaningLog */
                return empty($model->disinfection_type_by) ? null : $model->disinfectionTypeStyle;
            },
        ],
        [
            'attribute'=>'disinfection_auto_by',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLog */
                return empty($model->disinfection_auto_by) ? null : $model->disinfectionAutoBy->fxToolsMachines->name;
            },
        ],
        [
            'attribute'=>'disinfection_auto_agents_by',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLog */
                return empty($model->disinfection_auto_agents_by) ? null : $model->disinfectionAutoAgentsBy->fxToolsAgents->name;
            },
        ],
        [
            'attribute'=>'disinfection_auto_start',
            'format'=>'datetime',
            'filterWidgetOptions'=>
                [
                    'model' => $searchModel,
                    'attribute' => 'disinfection_auto_start',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ],
            'filterType'=>GridView::FILTER_DATE,     // Фильтр для таблицы
        ],
        [
            'attribute'=>'disinfection_auto_end',
            'format'=>'datetime',
            'filterWidgetOptions'=>
                [
                    'model' => $searchModel,
                    'attribute' => 'disinfection_auto_end',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ],
            'filterType'=>GridView::FILTER_DATE,     // Фильтр для таблицы
        ],
        [
            'attribute'=>'disinfection_manual_by',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLog */
                return empty($model->disinfection_manual_by) ? null : $model->disinfectionManualBy->fxToolsAgents->name;
            },
        ],
        [
            'attribute'=>'disinfection_manual_start',
            'format'=>'datetime',
            'filterWidgetOptions'=>
                [
                    'model' => $searchModel,
                    'attribute' => 'disinfection_manual_start',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ],
            'filterType'=>GridView::FILTER_DATE,     // Фильтр для таблицы
        ],
        [
            'attribute'=>'disinfection_manual_end',
            'format'=>'datetime',
            'filterWidgetOptions'=>
                [
                    'model' => $searchModel,
                    'attribute' => 'disinfection_manual_end',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ],
            'filterType'=>GridView::FILTER_DATE,     // Фильтр для таблицы
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
        ],
        [
            'attribute'=>'status_log',
            'format'=>'html',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLog */
                return empty($model->status_log) ? null : $model->statusLogStyle;
            },
            'filterWidgetOptions'=>
                [
                    // setting filter widget
                    'data' => $searchModel->statusLogList,
                    'options' => ['placeholder' => ' ...'],
                    'hideSearch' => true,
                    'pluginOptions' =>
                        [
                            'allowClear' => true,
                            'autoUpdateInput' => false,
                        ]
                ],
            'filterType'=>GridView::FILTER_SELECT2,     // Фильтр для таблицы
        ],
//        [
//            'class' => 'yii\grid\ActionColumn',
//            'order' => DynaGrid::ORDER_FIX_RIGHT,
//            'options' => ['width' => '4%'],
//            'template' => '{update} {delete}',
//            'buttons' => [
////                'view' => function ($url, $model) {
////                    /* @var $model app\modules\endoscopy\models\CleaningLog */
////                    return Html::a(Icon::show('eye'), ['/endoscopy/cleaning-log/view', 'id' => $model->id, 'returnUrl' => Helper::getReturnUrl()], [
////                        'title' => \Yii::t('app', 'view of «{attribute}»', ['attribute' => \Yii::t('app', 'Data')]),
////                    ]);
////                },
//                'update' => function ($url, $model, $key) {
//                    /* @var $model app\modules\endoscopy\models\CleaningLog */
//                    return Html::a(
//                        Icon::show('pencil'),
//                        ['/endoscopy/cleaning-log/update', 'id' => $model->id, 'form' => $model::FORM_TYPE_AJAX, 'returnUrl' => Helper::getReturnUrl()],
//                        [
//                            'class' => 'activity-view-link',
//                            'title' => \Yii::t('app', 'Change data'),
//                            'data-toggle' => 'modal',
//                            'data-target' => '#modal-cleaning-log',
//                            'data-id' => $key,
//                            'data-pjax' => '0',
//                        ]   );
//                },
////                'update' => function ($url, $model) {
////                    /* @var $model app\modules\endoscopy\models\CleaningLog */
////                    return Html::a(Icon::show('pencil'), ['/endoscopy/cleaning-log/update', 'id' => $model->id, 'returnUrl' => Helper::getReturnUrl()], [
////                        'title' => \Yii::t('app', 'Change data'),
////                    ]);
////                },
//                'delete' => function ($url, $model) {
//                    /* @var $model app\modules\endoscopy\models\CleaningLog */
//                    return Html::a(Icon::show('trash'), null, [
//                        'data-url' => Url::toRoute(['/endoscopy/cleaning-log/delete', 'id' => $model->id, 'returnUrl' => Helper::getReturnUrl()]),
//                        'onclick' => "$('#delete-category-confirmation').attr('data-url', $(this).attr('data-url')).removeAttr('data-items').modal('show');",
//                        'title' => \Yii::t('app', 'Delete data'),
//                    ]);
//                },
//            ],
//        ],
    ];

//    /** Кнопка добавления */
//    /** @var $button */
//    $button = Html::button(Icon::show('plus-circle'), [
//        'value' => Url::toRoute(['/endoscopy/cleaning-log/update', 'form' => CleaningLog::FORM_TYPE_AJAX, 'returnUrl' => Helper::getReturnUrl()]),
//        'class' => 'btn btn-' . GridView::TYPE_WARNING . ' btn-sm',
//        'title' => \Yii::t('app', 'Add «{attribute}»', ['attribute' => Yii::t('app', 'Cleaning Log')]),
//        'onclick' =>
//            '   $("#modal-cleaning-log").modal("show")
//                                        .find(".modal-header h4").text("' . \Yii::t('app', 'Adding data of «{attribute}»', ['attribute' => Yii::t('app', 'Cleaning log')]) . '")
//                                        .end()
//                                        .find(".modal-body")
//                                        .load($(this).attr("value"));
//                                            ',
//    ]);

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
                'heading' => Icon::show('sitemap') . Html::encode(Yii::t('app', 'General of data')),
                'after' => false,
                'before' => false,
            ],
//            'beforeHeader'=>[
//                [
//                    'columns'=>[
//                        ['content'=>'Основные данные', 'options'=>['colspan'=>4, 'class'=>'text-center ' . ColorCSS::BG_GREEN]],
//                        ['content'=>'Header Before 2', 'options'=>[/*'rowspan' => 2,*/ 'class'=>'text-center warning']],
//                        ['content'=>'Header Before 3', 'options'=>['colspan'=>3, 'class'=>'text-center warning']],
//                    ],
//                    'options'=>['class'=>'skip-export'] // remove this row from export
//                ]
//            ],
            'toolbar' => [
                ['content' =>
//                    $button .
//                    $buttonRemoveAll .
                    ' ' . Html::button(Icon::show('search'), [
                        'id' => 'ButtonSearch',
                        'value' => Url::toRoute(['/endoscopy/cleaning-log/search']),
                        'class' => 'btn btn-' . GridView::TYPE_PRIMARY . ' btn-sm',
                        'title' => \Yii::t('app', 'Search by parameters'),
                        'onclick' =>
                            '   $("#modal-cleaning-log").modal("show")
                                                        .find(".modal-header h4").text("' . \Yii::t('app', 'Search by parameters') . '").end()
                                                        .find(".modal-dialog").removeClass().addClass("modal-dialog ' . Modal::SIZE_LARGE . '").end()
                                                        .find(".modal-body")
                                                        .load($(this).attr("value"));
                                                            ',
                    ]) . ' ' .
                    Html::a(Icon::show('repeat'), ['index'], [
                        'class' => 'btn btn-default btn-sm',
                        'id' => 'refreshButton',
                        'title' => \Yii::t('app', 'Update'),
                    ])
                ],
                ['content' => '{dynagrid}'],
                '{toggleData}',
            ],
        ],
        'options' => ['id' => 'dynagrid-'.$searchModel->formName()] // уникальный идентификатор для настройки таблицы
    ]);
    ?>

</div>

<?php

$titleNameUpdate = \Yii::t('app', 'Editing of «{attribute}»', ['attribute' => Yii::t('app', 'Cleaning log')]);

$script = <<< JS

$(".activity-view-link").click(function() {
    $.get(
        $(this).attr("href"),
        {
            id: $(this).closest("tr").data("key"),
        },
        function (data) {
            $("#modal-cleaning-log").modal("show")
             .find(".modal-header h4").text("{$titleNameUpdate}").end()
             .find('.modal-body').html(data);
        }
    );
});

JS;

$this->registerJs($script);
?>
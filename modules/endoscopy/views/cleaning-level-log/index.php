<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;

use igor162\grid\GridView;
use igor162\dynagrid\DynaGrid;
use kartik\icons\Icon;
use kartik\widgets\AlertBlock;

use igor162\RemoveButton\RemoveModal;

use app\widgets\actions\Helper;
use app\modules\endoscopy\models\CleaningLevelLog;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\endoscopy\models\search\CleaningLevelLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Cleaning Levels');
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
<div class="cleaning-level-log-index">

    <?
    $columns = [
        ['class' => 'yii\grid\SerialColumn'],
//        'id',
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
            'attribute'=>'staff_by',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLevelLog */
                return empty($model->staff_by) ? null : $model->staffBy->last_name;
            },
        ],
        [
            'attribute'=>'level_1_add_staff_1',
            'format'=>'html',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLevelLog */
                return empty($model->level_1_add_staff_1) ? null : $model->level1StaffStyle;
            },
            'filterWidgetOptions'=>
                [
                    // setting filter widget
                    'data' => $searchModel->statementsTwoList,
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
        [
            'attribute'=>'level_1_add_tools_2',
            'format'=>'html',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLevelLog */
                return empty($model->level_1_add_tools_2) ? null : $model->level1ToolsStyle;
            },
            'filterWidgetOptions'=>
                [
                    // setting filter widget
                    'data' => $searchModel->statementsTwoList,
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
        [
            'attribute'=>'level_2_test_1',
            'format'=>'html',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLevelLog */
                return empty($model->level_2_test_1) ? null : $model->level2Style;
            },
            'filterWidgetOptions'=>
                [
                    // setting filter widget
                    'data' => $searchModel->statementsTwoList,
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
        [
            'attribute'=>'level_3_clear_1',
            'format'=>'html',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLevelLog */
                return empty($model->level_3_clear_1) ? null : $model->level3Style;
            },
            'filterWidgetOptions'=>
                [
                    // setting filter widget
                    'data' => $searchModel->statementsThreeList,
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
        [
            'attribute'=>'level_4_test_clear_2',
            'format'=>'html',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLevelLog */
                return empty($model->level_4_test_clear_2) ? null : $model->level4Style;
            },
            'filterWidgetOptions'=>
                [
                    // setting filter widget
                    'data' => $searchModel->statementsTwoList,
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
        [
            'attribute'=>'level_5_disinfection_manual',
            'format'=>'html',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLevelLog */
                return empty($model->level_5_disinfection_manual) ? null : $model->level5ManualStyle;
            },
            'filterWidgetOptions'=>
                [
                    // setting filter widget
                    'data' => $searchModel->statementsThreeList,
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
        [
            'attribute'=>'level_5_disinfection_auto',
            'format'=>'html',
            'value'=>function ($model) {
                /* @var $model app\modules\endoscopy\models\CleaningLevelLog */
                return empty($model->level_5_disinfection_auto) ? null : $model->level5AutoStyle;
            },
            'filterWidgetOptions'=>
                [
                    // setting filter widget
                    'data' => $searchModel->level5AutoList,
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
//            'attribute'=>'level_6_cleaning_tools',
//            'format'=>'html',
//            'value'=>function ($model) {
//                /* @var $model app\modules\endoscopy\models\CleaningLevelLog */
//                return empty($model->level_6_cleaning_tools) ? null : $model->level6Style;
//            },
//            'filterWidgetOptions'=>
//                [
//                    // setting filter widget
//                    'data' => $searchModel->statementsTwoList,
//                    'options' => ['placeholder' => ' ...'],
//                    'hideSearch' => true,
//                    'pluginOptions' =>
//                        [
//                            'allowClear' => true,
//                            'autoUpdateInput' => false,
//                        ]
//                ],
//            'filterType'=>GridView::FILTER_SELECT2,     // Фильтр для таблицы
//        ],
        //'comment_history',
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
            'filterType' => GridView::FILTER_DATE,     // Фильтр для таблицы
        ],
//        [
//            'class' => 'yii\grid\ActionColumn',
//            'order' => DynaGrid::ORDER_FIX_RIGHT,
//            'options' => ['width' => '4%'],
//            'template' => '{view} {update} {delete}',
//            'buttons' => [
////                'view' => function ($url, $model) {
////                    /* @var $model app\modules\endoscopy\models\CleaningLevelLog */
////                    return Html::a(Icon::show('eye'), ['/endoscopy/cleaning-level-log/view', 'id' => $model->id, 'returnUrl' => Helper::getReturnUrl()], [
////                        'title' => \Yii::t('app', 'view of «{attribute}»', ['attribute' => \Yii::t('app', 'Data')]),
////                    ]);
////                },
//                'update' => function ($url, $model, $key) {
//                    /* @var $model app\modules\endoscopy\models\CleaningLevelLog */
//                    return Html::a(
//                        Icon::show('pencil'),
//                        ['/endoscopy/cleaning-level-log/update', 'id' => $model->id, 'form' => $model::FORM_TYPE_AJAX, 'returnUrl' => Helper::getReturnUrl()],
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
////                    /* @var $model app\modules\endoscopy\models\CleaningLevelLog */
////                    return Html::a(Icon::show('pencil'), ['/endoscopy/cleaning-level-log/update', 'id' => $model->id, 'returnUrl' => Helper::getReturnUrl()], [
////                        'title' => \Yii::t('app', 'Change data'),
////                    ]);
////                },
//                'delete' => function ($url, $model) {
//                    /* @var $model app\modules\endoscopy\models\CleaningLevelLog */
//                    return Html::a(Icon::show('trash'), null, [
//                        'data-url' => Url::toRoute(['/endoscopy/cleaning-level-log/delete', 'id' => $model->id, 'returnUrl' => Helper::getReturnUrl()]),
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
//        'value' => Url::toRoute(['/endoscopy/cleaning-level-log/update', 'form' => CleaningLevelLog::FORM_TYPE_AJAX, 'returnUrl' => Helper::getReturnUrl()]),
//        'class' => 'btn btn-' . GridView::TYPE_WARNING . ' btn-sm',
//        'title' => \Yii::t('app', 'Add «{attribute}»', ['attribute' => Yii::t('app', 'Cleaning Level')]),
//        'onclick' =>
//            '   $("#modal-cleaning-log").modal("show")
//                                        .find(".modal-header h4").text("' . \Yii::t('app', 'Adding data of «{attribute}»', ['attribute' => Yii::t('app', 'Cleaning level')]) . '")
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
                'heading' => Icon::show('signal') . Html::encode(Yii::t('app', 'General of data')),
                'after' => false,
                'before' => false,
            ],
            'toolbar' => [
                ['content' =>
//                    $button .
//                    $buttonRemoveAll .
                    ' ' . Html::button(Icon::show('search'), [
                        'id' => 'ButtonSearch',
                        'value' => Url::toRoute(['/endoscopy/cleaning-level-log/search']),
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

$titleNameUpdate = \Yii::t('app', 'Editing of «{attribute}»', ['attribute' => Yii::t('app', 'Cleaning level')]);

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
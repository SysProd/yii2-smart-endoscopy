<?php

/* @var $this yii\web\View */
/* @var $searchModel app\modules\security\models\search\AuthItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

use yii\bootstrap\Modal;

use igor162\grid\GridView;
use igor162\dynagrid\DynaGrid;
use igor162\adminlte\ColorCSS;

use kartik\widgets\AlertBlock;
use kartik\icons\Icon;
use kartik\editable\Editable;
use kartik\depdrop\DepDrop;

use app\modules\security\models\AuthItem;

use igor162\RemoveButton\RemoveModal;
use igor162\RemoveButton\RemoveAllButton;

$this->title = \Yii::t('app', 'Access rights');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= RemoveModal::widget([
    'bodyTittle' => 'Are you sure you want to delete this item?'
]); ?>

<div class="auth-item-index">

    <?php
    Modal::begin([
        'size' => Modal::SIZE_LARGE,
        'header' => '<h4 class="text-left" style="color: #000; font-size: 20px; font-weight: 500;"></h4>',
        'closeButton' => false,
        'toggleButton' => false,
        'options' => [
            'id' => 'modal-auth_item',
            'tabindex' => false // important for Select2 to work properly
        ],
    ]);
    echo "<div id='modalContent-auth_item'> </div>";
    Modal::end();

    echo AlertBlock::widget([
        'useSessionFlash' => true,
        'type' => AlertBlock::TYPE_GROWL,
    ]);
    ?>

    <?php
    $columns = [
        [
            'class' => '\igor162\grid\CheckboxColumn',
            'hAlign' => GridView::ALIGN_LEFT,
            'vAlign' => GridView::ALIGN_MIDDLE,
            'options' => ['width' => '2%']
        ],
        [
            'attribute' => 'name',
            'format' => 'html',
            'value' => function ($model) {
                /* @var $model app\modules\security\models\AuthItem */
                return Html::a(Icon::show('pencil-square', ['class' => 'fa-lg']) . $model->name, ["/security/auth-item/update", "id" => $model->name]);
            }
        ],
        [
            'class' => 'kartik\grid\EditableColumn',
            'attribute' => 'description',
            'editableOptions' => [
                'size' => 'lg',
                'formOptions' => [
                    'action' => ['/security/auth-item/edit-description'],
                ],
            ],
        ],
        [
            'class' => 'kartik\grid\EditableColumn',
            'options' => ['width' => '10%'],
            'attribute' => 'type',
            'filterWidgetOptions' =>
                [    // setting filter widget
                    'data' => $searchModel->typesList,
                    'options' => ['placeholder' => ' ...'],
                    'hideSearch' => true,
                    'pluginOptions' =>
                        [
                            'allowClear' => true,
                            'autoUpdateInput' => false,
                        ]
                ],
            'editableOptions' =>
                [
                    'asPopover' => true,
                    'format' => DepDrop::TYPE_SELECT2,
                    'inputType' => Editable::INPUT_SELECT2,
                    'data' => $searchModel->typesList,
                    'options' =>
                        [
                            'class' => 'form-control',
                            'data' => $searchModel->typesList,
                            'options' => ['placeholder' => ' ...',],
                            'hideSearch' => true,
                            'pluginOptions' =>
                                [
                                    'tags' => true,
//                                        'allowClear' => true,
                                ],
                        ],
                    'formOptions' => [
                        'action' => ['/security/auth-item/edit-type'],
                    ],
                ],
            'filterType' => GridView::FILTER_SELECT2,     // Фильтр для таблицы
            'value' => function ($model) {
                /* @var $model app\modules\security\models\AuthItem */
                return $model->types;
            },
        ],
        [
            'attribute' => 'created_at',
            'format' => 'datetime',
            'filterWidgetOptions' =>
                [
                    'model' => $searchModel,
                    'attribute' => 'add_date',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ],
            'filterType' => GridView::FILTER_DATE,     // Фильтр для таблицы
        ],
        [
            'attribute' => 'updated_at',
            'format' => 'datetime',
            'filterWidgetOptions' =>
                [
                    'model' => $searchModel,
                    'attribute' => 'add_date',
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ],
            'filterType' => GridView::FILTER_DATE,     // Фильтр для таблицы
        ],
        [
            'class' => '\yii\grid\ActionColumn',
            'options' => ['width' => '50'],
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model) {
                    /* @var $model app\modules\security\models\AuthItem */
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model->name], [
                        'data' => [
                            'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                            'method' => 'post',
                        ],
                        'title' => \Yii::t('app', 'Delete of «{attribute}» #{item}', ['attribute' => \Yii::t('app', 'element'), 'item' => Html::encode($model->name)]),
                    ]);
                },
            ]
        ],
    ];

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'export' => false,
        'rowOptions' => function ($model) {
            /* @var $model app\modules\security\models\AuthItem */
            if ($model->type == AuthItem::TYPE_OPERATION) {
                return ['style' => 'background-color: #00a65a4d !important;'];
            } else if ($model->type == AuthItem::TYPE_TASK) {
                return ['style' => 'background-color: #f39c1280 !important;'];
            } else if ($model->type == AuthItem::TYPE_ROLE) {
                return ['style' => 'background-color: #7773c266 !important;'];
            }
        },
        'columns' => $columns,
        'panel' => [
            'type' => GridView::TYPE_DANGER,
            'heading' => Icon::show('lock', ['class' => 'fa-lg']) . \Yii::t('app', 'Access rights'),
            'afterOptions' => false,
        ],
        'toolbar' => [
            ['content' =>
                Html::button(Icon::show('plus-circle'), [
                    'id' => 'ButtonCreate',
                    'value' => Url::toRoute(['/security/auth-item/create']),
                    'class' => 'btn btn-warning btn-sm',
                    'title' => \Yii::t('app', 'Create a «{attribute}»', ['attribute' => \Yii::t('app', 'Access rule')]),
                    'onclick' =>
                        '   $("#modal-auth_item").modal("show")
                                        .find(".modal-header h4").text("' . \Yii::t('app', 'Adding data of «{attribute}»', ['attribute' => Yii::t('app', 'Access rule')]) . '")
                                        .end()
                                        .find(".modal-dialog").removeClass().addClass("modal-dialog modal-lg")
                                        .end()
                                        .find("#modalContent-auth_item")
                                        .load($(this).attr("value"));
                                            ',
                ]) . ' ' .
                RemoveAllButton::widget([
                    'url' => Url::to(
                        [ 'remove-items' ]
                    ),
                    'gridSelector' => '.grid-view',
                    'modalSelector' => 'delete-category-confirmation',
                    'htmlOptions' => [
                        'class' => 'btn btn-' . GridView::TYPE_DANGER . ' btn-sm',
                    ],
                ]) . ' ' .
                Html::a(Icon::show('repeat'), ['index'], [
                    'class' => 'btn btn-default btn-sm',
                    'title' => \Yii::t('app', 'Update'),
                ])
            ],
//            '{export}',
            '{toggleData}',
        ],
        'persistResize' => false,
        'bootstrap' => true,
        'bordered' => false,
        'striped' => true,
        'condensed' => false,
        'responsive' => true,
        'responsiveWrap' => true,
        'hover' => true,
        'perfectScrollbar' => true,
    ]); ?>
</div>
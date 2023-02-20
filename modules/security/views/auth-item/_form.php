<?php

/* @var $this yii\web\View */
/* @var $model app\modules\security\models\AuthItem */
/* @var $form yii\widgets\ActiveForm */
/* @var array $items */
/* @var array $children */

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

use yii\widgets\ActiveForm;

use igor162\adminlte\widgets\Box;

use kartik\icons\Icon;
use kartik\select2\Select2;
use kartik\typeahead\TypeaheadBasic;

use igor162\MultiSelect\MultiSelect;
use app\modules\security\models\AuthItem;

$smallBox = isset($model->name) ? ' [' . Html::encode($model->types . ' #' . $model->name) . ']' : null;    // вывод блока small "при изменении модели"
?>

<?php $form = ActiveForm::begin([
    'id' => $model->formName(),
    'enableClientValidation' => true,
    'enableAjaxValidation' => true,
]); ?>


<?php
if(!$model->isNewRecord){
    Box::begin([
        'type' => Box::TYPE_SUCCESS,
        'title' => Html::encode($this->title) . '<small>' . $smallBox . '</small>',
        /*    'footer' =>
                [
                    Html::a(Icon::show('arrow-circle-left', ['class' => 'fa-lg']) . \Yii::t('app', 'Back'), ["/security/auth-item/index",], ['class' => 'btn btn-danger']),
                    Html::submitButton($model->isNewRecord ? Icon::show('save', ['class' => 'fa-lg']) . \Yii::t('app', 'Save and go back') : Icon::show('save', ['class' => 'fa-lg']) . \Yii::t('app', 'Edit and go back'), ['class' => 'btn btn-warning', 'name' => 'action', 'value' => 'next',]),
                    Html::submitButton($model->isNewRecord ? Icon::show('save', ['class' => 'fa-lg']) . \Yii::t('app', 'Save') : Icon::show('save', ['class' => 'fa-lg']) . \Yii::t('app', 'Edit'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'name' => 'action', 'value' => 'save',]),
                ],*/
    ]);
} ?>

<div class="row"> <!-- start:row -->
    <div class="col-sm-12">
        <?= $form->field($model, 'name')->widget(
            TypeaheadBasic::classname(), [
            'data' => ArrayHelper::map(AuthItem::find()->all(), 'name', 'name'),
            'dataset' => ['limit' => 10],
            'options' => [
                'title' => $model->getAttributeLabel("name"),
                'placeholder' => $model->getAttributeLabel("name") . ' ...',
            ],
            'pluginOptions' => [
                'highlight' => true,
                'minLength' => 3,
            ],
        ]); ?>
    </div>
</div><!-- end:row  -->
<div class="row"> <!-- start:row -->
    <div class="col-sm-12">
        <!--    Select shape_tangibles-->
        <?= $form->field($model, "type")->widget(
            Select2::classname(), [
            'data' => $model->typesList,
            'hideSearch' => true,
            'options' =>
                [
                    'placeholder' => $model->getAttributeLabel("type") . ' ...',
                    'title' => $model->getAttributeLabel("type"),
                    'onchange' => '$.post( "' . \Yii::$app->urlManager->createUrl("security/auth-item/lists-auth-item") . '", { name: $("input#authitem-name").val(), type: $(this).val() }, function(data){ $("select#rules").html(data); });',
                ],

            'pluginOptions' => [
                'allowClear' => true
            ],]) ?>

    </div>
</div><!-- end:row  -->
<div class="row"> <!-- start:row -->
    <div class="col-sm-12">
        <?= $form->field($model, 'description')->widget(
            TypeaheadBasic::classname(), [
            'data' => ArrayHelper::map(AuthItem::find()->all(), 'description', 'description'),
            'dataset' => ['limit' => 10],
            'options' => [
                'title' => $model->getAttributeLabel("description"),
                'placeholder' => $model->getAttributeLabel("description") . ' ...',
            ],
            'pluginOptions' => [
                'highlight' => true,
                'minLength' => 10,
            ],
        ]); ?>
    </div>
</div><!-- end:row  -->
<div class="row"> <!-- start:row -->
    <div class="col-sm-12">
        <?= $form->field($model, 'children')->widget(MultiSelect::className(), [
            'items' => $items,
            'selectedItems' => $children,
            'ajax' => false,
        ]) ?>
    </div>
</div><!-- end:row  -->

<div class="box-footer">
    <div class="pull-left">
        <? if($model->isNewRecord){ ?>
        <?= Html::button(Icon::show('times-circle', ['class' => 'fa-lg']).\Yii::t('app', 'Cancel'),['class' => 'btn btn-danger', 'data-dismiss' => 'modal', 'aria-hidden' => true,]) ?>
        <? }else{ ?>
        <?= Html::a(Icon::show('arrow-circle-left', ['class' => 'fa-lg']).\Yii::t('app', 'Back'), [ "/security/auth-item/index", ], ['class' => 'btn btn-danger']) ?>
        <? } ?>
    </div>
    <div class="pull-right">
        <?= Html::submitButton($model->isNewRecord ? Icon::show('save', ['class' => 'fa-lg']).\Yii::t('app', 'Save') : Icon::show('save', ['class' => 'fa-lg']).\Yii::t('app', 'Edit'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'name' => 'action', 'value' => 'save',]) ?>
    </div>
</div>

<?php if(!$model->isNewRecord){ Box::end(); } ?>

<?php ActiveForm::end(); ?>


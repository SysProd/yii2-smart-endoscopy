<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;

use kartik\widgets\DatePicker;
use kartik\select2\Select2;
use kartik\money\MaskMoney;

/* @var $this yii\web\View */
/* @var $model app\modules\endoscopy\models\search\RfidTagsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="rfid-tags-search">

    <?php $form = ActiveForm::begin([
        'id' => $model->formName(),
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'coded_key')->widget(MaskedInput::className(), ['mask' => '9', 'clientOptions' => ['repeat' => 1, 'greedy' => false]]) ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'status_tied')->widget(
                Select2::classname(), [
                'data' => $model->statusTiedList,
                'hideSearch' => true,
                'options' => [
                    'placeholder' => ' ...',
                ],
                'pluginOptions' => [
                    'allowClear' => false,
                    'minimumInputLength' => 2,
                ],
            ]); ?>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'created_by')->widget(
                Select2::classname(), [
                'data' => $model->usersList,
                'hideSearch' => true,
                'options' => [
                    'placeholder' => ' ...',
                    'title' => $model->getAttributeLabel("created_by"),
                ],
                'pluginOptions' => [
                    'allowClear' => false,
                    'minimumInputLength' => 3,
                ],
            ]) ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'updated_by')->widget(
                Select2::classname(), [
                'data' => $model->usersList,
                'hideSearch' => true,
                'options' => [
                    'placeholder' => ' ...',
                    'title' => $model->getAttributeLabel("updated_by"),
                ],
                'pluginOptions' => [
                    'allowClear' => false,
                    'minimumInputLength' => 3,
                ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'created_at_from')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => ''],
                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                'pluginOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    'todayBtn' => true,
                    'format' => 'dd-mm-yyyy'
                ]
            ]); ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'created_at_till')
                ->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => ''],
                    'type' => DatePicker::TYPE_COMPONENT_APPEND,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'updated_at_from')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => ''],
                'type' => DatePicker::TYPE_COMPONENT_APPEND,
                'pluginOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    'todayBtn' => true,
                    'format' => 'dd-mm-yyyy'
                ]
            ]); ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'updated_at_till')
                ->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => ''],
                    'type' => DatePicker::TYPE_COMPONENT_APPEND,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'dd-mm-yyyy'
                    ]
                ]); ?>
        </div>
    </div>

    <div class="box-footer">
        <div class="pull-left">
            <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        </div>
        <div class="pull-right">
            <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

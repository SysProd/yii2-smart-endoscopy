<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;

use kartik\icons\Icon;
use kartik\select2\Select2;

use app\modules\counterparty\models\Counterparty;
use app\modules\staff\models\Staff;


/* @var $this yii\web\View */
/* @var $model app\modules\data\models\Phone */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="panel-body"> <!-- start:panel-body-->

    <?php $form = ActiveForm::begin([
        'id'=>$model->formName(),
        'enableClientValidation' => true,
        'fieldConfig' => ['template' => "{input}\n{error}"],
    ]); ?>

    <?= $form->field($model, 'counterparty_id')->widget(
        Select2::classname(),
        [
            'data' => ArrayHelper::map(Counterparty::find()/*->where(['department_id' => $profile->department_id])*/->all(),'id','short_name'),
            'hideSearch' => false,
            'options' => ['placeholder' => $model->getAttributeLabel("counterparty_id").' ...',],
            'pluginOptions' => [
                    'allowClear' => true,
//                    'minimumInputLength' => 3,
            ],
        ]) ?>

    <?= $form->field($model, 'user_id')->widget(
        Select2::classname(),
        [
            'data' => ArrayHelper::map(Staff::find()/*->where(['department_id' => $profile->department_id])*/->all(),'id','fullName'),
            'hideSearch' => false,
            'options' => ['placeholder' => $model->getAttributeLabel("user_id").' ...',],
            'pluginOptions' => [
                    'allowClear' => true,
//                    'minimumInputLength' => 3,
            ],
        ]) ?>

    <?= $form->field($model, 'type_phone')->widget(
        Select2::classname(),
        [
            'data' => $model->getTypeList(),
            'hideSearch' => true,
            'options' => ['placeholder' => $model->getAttributeLabel("type_phone").' ...',],
        ]) ?>

    <?= $form->field($model, 'status_phone')->widget(
        Select2::classname(),
        [
            'data' => $model->getStatusList(),
            'hideSearch' => true,
            'options' => ['placeholder' => $model->getAttributeLabel("status_phone").' ...',],
            'pluginOptions' => [
//                    'allowClear' => true,
//                    'minimumInputLength' => 3,
        ],
        ]) ?>

    <?=  $form->field($model, "default_phone") ->checkBox([ 'class' => 'default_phone_check', 'label' => '', 'title' => \Yii::t('app', 'Basic phone') ], false)->label(\Yii::t('app', 'Basic')); ?>

    <?= $form->field($model, "phone_reference")->widget(MaskedInput::className(), ['mask' => '+7(999) 999-99-99', 'clientOptions' => [ 'removeMaskOnSubmit' => true ], 'options' => ['class' => 'form-control', 'placeholder' => $model->getAttributeLabel("type_phone").' ...',],])  ?>

<!--    --><?//= $form->field($model, 'phone_template')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'comment')->textInput(['maxlength' => true, 'placeholder' => $model->getAttributeLabel("comment").' ...']) ?>

    <div class="form-group">
        <?= Html::a(Icon::show('arrow-circle-left', ['class' => 'fa-lg']).\Yii::t('app', 'Back'), [ "/data/phone/index", ], ['class' => 'btn btn-danger']) ?>
        <?= Html::submitButton($model->isNewRecord ? Icon::show('save', ['class' => 'fa-lg']).\Yii::t('app', 'Save and go back') : Icon::show('save', ['class' => 'fa-lg']).\Yii::t('app', 'Edit and go back'), ['class' => 'btn btn-warning', 'name' => 'action', 'value' => 'next',]) ?>
        <?= Html::submitButton($model->isNewRecord ? Icon::show('save', ['class' => 'fa-lg']).\Yii::t('app', 'Save') : Icon::show('save', ['class' => 'fa-lg']).\Yii::t('app', 'Edit'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'name' => 'action', 'value' => 'save',]) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>

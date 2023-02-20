<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\modules\security\models\ResetPasswordAdmin*/

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\icons\Icon;
use kartik\checkbox\CheckboxX;


$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback',],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>",
    'inputOptions' => [ 'autocomplete' => 'off'],
];

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback',],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>",
    'inputOptions' => [ 'autocomplete' => 'off'],
];


$script = <<< JS

jQuery("form#{$model->formName()}").on("change", "#resetpasswordadmin-auto_generate", function(e, item) {

        if( this.checked ){
            $("#resetpasswordadmin-password").prop('disabled', true);
            $("#resetpasswordadmin-password_repeat").prop('disabled', true);
            $("#reset-send_mail").hide();
        }else{
            $("#resetpasswordadmin-password").prop('disabled', false);
            $("#resetpasswordadmin-password_repeat").prop('disabled', false);
            $("#reset-send_mail").show();
        }

});

JS;

$this->registerJs($script);
?>
<div class="shop-stuff-create">

    <?php $form = ActiveForm::begin([
        'id' => $model->formName(),
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
    ]); ?>

    <?= $form->field($model, 'password', $fieldOptions1)->passwordInput(['autofocus' => true, 'placeholder'=>$model->getAttributeLabel('password')])->label(false) ?>

    <?= $form->field($model, 'password_repeat', $fieldOptions2)->passwordInput(['placeholder'=>$model->getAttributeLabel('password_repeat')])->label(false) ?>

    <?= $form->field($model, "auto_generate", ['options' => ['class'=>'form-group has-warning']])->widget(
        CheckboxX::classname(),
        [
            'initInputType' => CheckboxX::INPUT_CHECKBOX,
            'autoLabel' => true,
            'options'=>
                [
                    'value'=>$model->auto_generate,
                ],
            'labelSettings' => [
                'position' => CheckboxX::LABEL_RIGHT
            ],
            'pluginOptions' => [
                'useNative' => false,
                'enclosedLabel' => true,
                'threeState' => false,
            ],
        ])->label(false) ?>

    <?= $form->field($model, "send_mail", ['options' => ['id' => 'reset-send_mail', 'class'=>'form-group has-warning']])->widget(
        CheckboxX::classname(),
        [
            'initInputType' => CheckboxX::INPUT_CHECKBOX,
            'autoLabel' => true,
            'options'=>
                [
                    'value'=>$model->send_mail,
                ],
            'labelSettings' => [
                'position' => CheckboxX::LABEL_RIGHT
            ],
            'pluginOptions' => [
                'useNative' => false,
                'enclosedLabel' => true,
                'threeState' => false,
            ],
        ])->label(false) ?>

    <div class="box-footer">
        <div class="pull-left">
            <?= Html::button(Icon::show('times-circle', ['class' => 'fa-lg']).\Yii::t('app', 'Cancel'),['class' => 'btn btn-danger', 'data-dismiss' => 'modal', 'aria-hidden' => true,]) ?>
        </div>
        <div class="pull-right">
            <?= Html::submitButton(Icon::show('save', ['class' => 'fa-lg']).\Yii::t('app', 'Apply'), ['class' => 'btn btn-primary', 'name' => 'action', 'value' => 'save',]) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php

/* @var $this yii\web\View */
/* @var $model app\modules\security\models\User */
/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\MaskedInput;

use kartik\widgets\ActiveForm;
use kartik\icons\Icon;

?>
<div class="panel-body"> <!-- start:panel-body-->

    <?php $form = ActiveForm::begin([
        'id' => $model->formName(),
        'enableClientValidation' => true,
    ]); ?>

    <div class="row"> <!-- start:row -->
        <div class="col-sm-12">
    <?= $form->field($model, 'username')->textInput(['placeholder'=>$model->getAttributeLabel('username'), 'maxlength' => true]) ?>
        </div>
    </div><!-- end:row  -->

    <div class="row"> <!-- start:row -->
        <div class="col-sm-12">
    <?= $form->field($model, 'email')->widget(MaskedInput::className(), [ 'clientOptions' => ['alias' => 'email',], 'options' => ['class' => 'form-control', 'placeholder' => $model->getAttributeLabel("email").' ...',] ]) ?>
        </div>
    </div><!-- end:row  -->

    <div class="box-footer">
        <div class="pull-left">
            <? if($model->isNewRecord){ ?>
                <?= Html::button(Icon::show('times-circle', ['class' => 'fa-lg']).\Yii::t('app', 'Cancel'),['class' => 'btn btn-danger', 'data-dismiss' => 'modal', 'aria-hidden' => true,]) ?>
            <? } ?>
        </div>
        <div class="pull-right">
            <?= Html::submitButton($model->isNewRecord ? Icon::show('save', ['class' => 'fa-lg']).\Yii::t('app', 'Save').' «'.\Yii::t('app', 'Authentication data').'»' : Icon::show('save', ['class' => 'fa-lg']).\Yii::t('app', 'Edit').' «'.\Yii::t('app', 'Authentication data').'»', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'name' => 'action', 'value' => 'save',]) ?>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>  <!-- .panel-body for tangibles-new-dock-->
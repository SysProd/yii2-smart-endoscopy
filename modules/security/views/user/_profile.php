<?php

/* @var $this yii\web\View */
/* @var $profile app\modules\security\models\UserProfile */
/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\icons\Icon;

?>

<div class="panel-body"> <!-- start:panel-body-->
    <?php $form = ActiveForm::begin([
        'id'=>$profile->formName(),
        'enableClientValidation' => true
    ]); ?>
    <div class="row"> <!-- start:row -->
        <div class="col-sm-12">
        </div>
    </div><!-- end:row  -->
    <div class="row"> <!-- start:row -->
        <div class="col-sm-12">
            <?= $form->field($profile, 'last_name')->textInput(['maxlength' => true]) ?>
        </div>
    </div><!-- end:row  -->
    <div class="row"> <!-- start:row -->
        <div class="col-sm-12">
            <?= $form->field($profile, 'first_name')->textInput(['maxlength' => true]) ?>
        </div>
    </div><!-- end:row  -->
    <div class="row"> <!-- start:row -->
        <div class="col-sm-12">
            <?= $form->field($profile, 'patronymic')->textInput(['maxlength' => true]) ?>
        </div>
    </div><!-- end:row  -->
    <div class="row"> <!-- start:row -->
        <div class="col-sm-12">
            <?= $form->field($profile, 'gender')->widget( Select2::classname(), [ 'data' => $profile->genderList, 'hideSearch' => true,]) ?>
        </div>
    </div><!-- end:row  -->

    <div class="box-footer">
        <div class="pull-left">
            <? if($profile->isNewRecord){ ?>
                <?= Html::button(Icon::show('times-circle', ['class' => 'fa-lg']).\Yii::t('app', 'Cancel'),['class' => 'btn btn-danger', 'data-dismiss' => 'modal', 'aria-hidden' => true,]) ?>
            <? }else{ ?>
                <?= Html::a(Icon::show('arrow-circle-left', ['class' => 'fa-lg']).\Yii::t('app', 'Back'), [ "/security/user/index", ], ['class' => 'btn btn-danger']) ?>
            <? } ?>
        </div>
        <div class="pull-right">
            <?= Html::submitButton($profile->isNewRecord ? Icon::show('save', ['class' => 'fa-lg']).\Yii::t('app', 'Save').' «'.\Yii::t('app', 'Profile').'»' : Icon::show('save', ['class' => 'fa-lg']).\Yii::t('app', 'Edit').' «'.\Yii::t('app', 'Profile').'»', ['class' => $profile->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'name' => 'action', 'value' => 'save',]) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>  <!-- .panel-body for tangibles-new-dock-->

<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

use yii\widgets\ActiveForm;

use kartik\icons\Icon;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\modules\endoscopy\models\CleaningLog */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="panel-body"> <!-- start:panel-body-->

    <?php $form = ActiveForm::begin([
        'id' => $model->formName(),
        'enableClientValidation' => (Yii::$app->request->get('form') === $model::FORM_TYPE_AJAX) ? false : true,
        'enableAjaxValidation' => (Yii::$app->request->get('form') === $model::FORM_TYPE_AJAX) ? true : false,
    ]); ?>


    <?= $form->field($model, 'staff_by')->widget(
        Select2::classname(), [
        'data' => $model->staffList,
        'hideSearch' => false,
        'options' => [
            'placeholder' => ' ...',
            'title' => $model->getAttributeLabel("staff_by"),
        ],
        'pluginOptions' => [
            'allowClear' => true,
//            'minimumInputLength' => 3,
        ],
    ]); ?>

    <?= $form->field($model, 'tools_by')->textInput() ?>

    <?= $form->field($model, 'test_tightness_by')->textInput() ?>

    <?= $form->field($model, 'cleaning_agents_by')->textInput() ?>

    <?= $form->field($model, 'cleaning_start')->textInput() ?>

    <?= $form->field($model, 'cleaning_end')->textInput() ?>

    <?= $form->field($model, 'test_qualities_cleaning_date')->textInput() ?>

    <?= $form->field($model, 'test_qualities_cleaning_status')->textInput() ?>


    <?= $form->field($model, 'disinfection_type_by')->widget(
        Select2::classname(), [
        'data' => $model->disinfectionTypeList,
        'hideSearch' => true,
        'options' => [
            'placeholder' => ' ...',
            'title' => $model->getAttributeLabel("disinfection_type_by"),
        ],
    ]); ?>

    <?= $form->field($model, 'disinfection_auto_by')->textInput() ?>

    <?= $form->field($model, 'disinfection_auto_start')->textInput() ?>

    <?= $form->field($model, 'disinfection_auto_end')->textInput() ?>

    <?= $form->field($model, 'disinfection_manual_by')->textInput() ?>

    <?= $form->field($model, 'disinfection_manual_start')->textInput() ?>

    <?= $form->field($model, 'disinfection_manual_end')->textInput() ?>

    <?= $form->field($model, 'cleaning_tools_start')->textInput() ?>

    <?= $form->field($model, 'cleaning_tools_end')->textInput() ?>


    <?/*= $form->field($model, 'updated_at')->textInput() */?>
    <?/*= $form->field($model, 'add_data')->textInput() */?>

    <div class="box-footer">
        <div class="pull-left">
            <?php if (Yii::$app->request->get('form') === $model::FORM_TYPE_AJAX) { ?>
                <?= Html::button(Icon::show('times-circle', ['class' => 'fa-lg']) . \Yii::t('app', 'Cancel'), ['class' => 'btn btn-danger', 'data-dismiss' => 'modal', 'aria-hidden' => true,]) ?>
            <?php } else { ?>
                <?= Html::a(Icon::show('arrow-circle-left', ['class' => 'fa-lg']) . \Yii::t('app', 'Back'), Yii::$app->request->get('returnUrl', ['index']), ['class' => 'btn btn-danger']) ?>
            <?php } ?>
        </div>
        <div class="pull-right">
            <?= Html::submitButton($model->isNewRecord ? Icon::show('save', ['class' => 'fa-lg']) . \Yii::t('app', 'Save') : Icon::show('save', ['class' => 'fa-lg']) . \Yii::t('app', 'Edit'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'name' => 'action', 'value' => 'save',]) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>  <!-- .panel-body -->

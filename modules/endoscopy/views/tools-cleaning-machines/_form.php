<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

use yii\widgets\ActiveForm;

use kartik\icons\Icon;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\modules\endoscopy\models\ToolsCleaningMachines */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="panel-body"> <!-- start:panel-body-->

    <?php $form = ActiveForm::begin([
        'id' => $model->formName(),
        'enableClientValidation' => (Yii::$app->request->get('form') === $model::FORM_TYPE_AJAX) ? false : true,
        'enableAjaxValidation' => (Yii::$app->request->get('form') === $model::FORM_TYPE_AJAX) ? true : false,
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mode')->textInput() ?>

    <?= $form->field($model, 'comment')->textInput(['maxlength' => true]) ?>

    <!--    --><?/*= $form->field($model, 'comment_history')->widget(\dosamigos\ckeditor\CKEditor::className(), [
        'options' => ['rows' => 6],
        'preset' => 'custom',
        'clientOptions' => [
            'height' => 150,
            'toolbarGroups' => [
                ['name' => 'undo'],
                ['name' => 'basicstyles', 'groups' => ['basicstyles', 'colors', 'cleanup']],
                ['name' => 'links', 'groups' => ['links', 'insert']],
                ['name' => 'paragraph', 'groups' => ['list', 'blocks']],
                ['name' => 'tools'],
            ],
            'removeButtons' => 'Subscript,Superscript,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe',
            'removePlugins' => 'elementspath',
            'resize_enabled' => false,
        ],
    ]) */?>

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

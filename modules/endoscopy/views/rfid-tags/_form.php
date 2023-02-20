<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

use yii\widgets\ActiveForm;

use kartik\icons\Icon;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model app\modules\endoscopy\models\RfidTags */
/* @var $form yii\widgets\ActiveForm */

$no = $model::No;
$agent = $model::Cln_Agent;
$tool = $model::Cln_Tool;
$machined = $model::Machined;
$statuses = $model::Statuses;
$staff = $model::Staff;

$script = <<< JS

var  typeSelectParam   = jQuery("form#{$model->formName()} [data-krajee-select2]#rfidtags-status_tied"),
     DivSelectTools    = jQuery("form#{$model->formName()} #select_fx_tools"),
     DivSelectStaff    = jQuery("form#{$model->formName()} #select_fx_staff"),
     DivSelectAgents   = jQuery("form#{$model->formName()} #select_fx_tools_agents"),
     DivSelectMachines = jQuery("form#{$model->formName()} #select_fx_tools_machines"),
     DivSelectStatuses = jQuery("form#{$model->formName()} #select_fx_tools_statuses");

     function ShowHideInput(vl){
          DivSelectTools.hide();
          DivSelectStaff.hide();
          DivSelectAgents.hide();
          DivSelectMachines.hide();
          DivSelectStatuses.hide();
          // console.log(vl);
          if(vl === "{$agent}"){
              DivSelectAgents.show();
          }else if(vl === "{$machined}"){
              DivSelectTools.show();
          }else if(vl === "{$tool}"){
              DivSelectMachines.show();
          }else if(vl === "{$statuses}"){
              DivSelectStatuses.show();
          }else if(vl === "{$staff}"){
              DivSelectStaff.show();
          }
     }

    ShowHideInput(typeSelectParam.find(":selected").val());

    typeSelectParam.on('change', function(e){
       ShowHideInput(typeSelectParam.find(":selected").val());
   });


JS;

$this->registerJs($script);

?>
<div class="panel-body"> <!-- start:panel-body-->

    <?php $form = ActiveForm::begin([
        'id' => $model->formName(),
        'enableClientValidation' => (Yii::$app->request->get('form') === $model::FORM_TYPE_AJAX) ? false : true,
        'enableAjaxValidation' => (Yii::$app->request->get('form') === $model::FORM_TYPE_AJAX) ? true : false,
    ]); ?>

    <?= $form->field($model, 'coded_key')->textInput() ?>

    <?= $form->field($model, 'comment')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status_tied')->widget(
        Select2::classname(), [
        'data' => $model->statusTiedList,
        'hideSearch' => true,
        'options' => [
            'placeholder' => ' ...',
            'title' => $model->getAttributeLabel("status_tied"),
        ],
    ]); ?>

    <?= $form->field($model, 'fx_tools',['options' => ['id' => 'select_fx_tools']])->widget(
        Select2::classname(), [
        'data' => $model->toolsList,
        'hideSearch' => false,
        'options' => [
            'placeholder' => ' ...',
            'title' => $model->getAttributeLabel("fx_tools"),
        ],
        'pluginOptions' => [
            'allowClear' => true,
//            'minimumInputLength' => 3,
        ],
    ]); ?>

    <?= $form->field($model, 'fx_staff',['options' => ['id' => 'select_fx_staff']])->widget(
        Select2::classname(), [
        'data' => $model->staffList,
        'hideSearch' => false,
        'options' => [
            'placeholder' => ' ...',
            'title' => $model->getAttributeLabel("fx_staff"),
        ],
        'pluginOptions' => [
            'allowClear' => true,
//            'minimumInputLength' => 3,
        ],
    ]); ?>

    <?= $form->field($model, 'fx_tools_agents',['options' => ['id' => 'select_fx_tools_agents']])->widget(
        Select2::classname(), [
        'data' => $model->agentsList,
        'hideSearch' => false,
        'options' => [
            'placeholder' => ' ...',
            'title' => $model->getAttributeLabel("fx_tools_agents"),
        ],
        'pluginOptions' => [
            'allowClear' => true,
//            'minimumInputLength' => 3,
        ],
    ]); ?>

    <?= $form->field($model, 'fx_tools_machines',['options' => ['id' => 'select_fx_tools_machines']])->widget(
        Select2::classname(), [
        'data' => $model->machinesList,
        'hideSearch' => false,
        'options' => [
            'placeholder' => ' ...',
            'title' => $model->getAttributeLabel("fx_tools_machines"),
        ],
        'pluginOptions' => [
            'allowClear' => true,
//            'minimumInputLength' => 3,
        ],
    ]); ?>

    <?= $form->field($model, 'fx_tools_statuses',['options' => ['id' => 'select_fx_tools_statuses']])->widget(
        Select2::classname(), [
        'data' => $model->statusesList,
        'hideSearch' => false,
        'options' => [
            'placeholder' => ' ...',
            'title' => $model->getAttributeLabel("fx_tools_statuses"),
        ],
        'pluginOptions' => [
            'allowClear' => true,
//            'minimumInputLength' => 3,
        ],
    ]); ?>

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

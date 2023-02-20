<?php

/* @var $this yii\web\View */
/* @var $model app\modules\staff\models\Staff */
/* @var $phonesForUser app\modules\staff\models\Phone[] */
/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

use yii\widgets\MaskedInput;

use yii\bootstrap\Modal;

use kartik\checkbox\CheckboxX;
use kartik\widgets\ActiveForm;
use kartik\widgets\AlertBlock;
use kartik\select2\Select2;
use kartik\icons\Icon;

use wbraganca\dynamicform\DynamicFormWidget;

use app\widgets\actions\Helper;

use app\modules\staff\models\Staff;
use app\modules\security\models\AuthItem;

$defaultPhoneMSG = \Yii::t('app','It should be noted the default phone for the user');
$script = <<< JS

jQuery("form#{$model->formName()} .dynamicform_wrapper .help-block").each(function(e, item) {
  helpBlock = $(item);
  // Выделить ошибки стелем
  if(helpBlock.text().length !== 0 ){ helpBlock.text('').parent().removeClass('has-error').addClass('has-error'); }
});

jQuery("form#{$model->formName()} .dynamicform_wrapper").on("afterInsert", function(e, item) {

        var \$hasInputmask = $(this).find('[data-plugin-inputmask]');
        if (\$hasInputmask.length > 0) {
            \$hasInputmask.each(function() {
                $(this).inputmask('remove');
                $(this).inputmask(eval($(this).attr('data-plugin-inputmask')));
            });
        }

});

jQuery("form#{$model->formName()} .dynamicform_wrapper").on("change", ".default_phone_check", function(e, item) {

    // разрешить выделить только один checkbox
    if( this.checked ){
    \$checked = $(".default_phone_check").filter('input:checked');
                if(\$checked.length >= 2){
                // поиск всех элементов "input:checked" и убрать все "checked"
                \$checked.each(function(indx) {
                    this.checked = false;
                });
                 this.checked = true;   // отметить выбранный элмент
                 }
                    }else{
            alert('{$defaultPhoneMSG}');
            }
});


JS;

$this->registerJs($script);
?>

<?php
Modal::begin([
    'size' => Modal::SIZE_LARGE,
    'header' => '<h4 class="text-left" style="color: #000; font-size: 20px; font-weight: 500;"></h4>',
    'closeButton' => false,
    'toggleButton' => false,
    'options' => [
        'id' => 'modal-formStaff',
        'tabindex' => false // important for Select2 to work properly
    ],
]);
echo "<div id='modalContent-formStaff'> </div>";
Modal::end();

echo AlertBlock::widget([
    'useSessionFlash' => true,
    'type' => AlertBlock::TYPE_GROWL,
]);
?>

    <div class="panel-body"> <!-- start:panel-body-->
        <?php $form = ActiveForm::begin([
            'id'=>$model->formName(),
            'enableClientValidation' => true,
            'enableAjaxValidation' => (Yii::$app->request->get('form') === Staff::FORM_TYPE_AJAX) ? true : false,
        ]); ?>
        <div class="row"> <!-- start:row -->
            <div class="col-sm-12">
            </div>
        </div><!-- end:row  -->
        <div class="row"> <!-- start:row -->
            <div class="col-sm-12">
                <?= $form->field($model, 'last_name', ['showLabels'=>false])->textInput(['maxlength' => true, 'placeholder' => $model->getAttributeLabel("last_name").' ...']); ?>
            </div>
        </div><!-- end:row  -->
        <div class="row"> <!-- start:row -->
            <div class="col-sm-12">
                <?= $form->field($model, 'first_name', ['showLabels'=>false])->textInput(['maxlength' => true, 'placeholder' => $model->getAttributeLabel("first_name").' ...']); ?>
            </div>
        </div><!-- end:row  -->
        <div class="row"> <!-- start:row -->
            <div class="col-sm-12">
                <?= $form->field($model, 'patronymic', ['showLabels'=>false])->textInput(['maxlength' => true, 'placeholder' => $model->getAttributeLabel("patronymic").' ...']); ?>
            </div>
        </div><!-- end:row  -->
        <div class="row"> <!-- start:row -->
            <div class="col-sm-12">
                <?= $form->field($model, 'gender', ['showLabels'=>false])->widget(
                    Select2::classname(), [
                    'data' => $model->genderList,
                    'hideSearch' => true,
                    'options' => [
                        'placeholder' => $model->getAttributeLabel("gender").' ...',
                        'title' => $model->getAttributeLabel("gender"),
                    ],
                ]); ?>
            </div>
        </div><!-- end:row  -->
        <div class="row"> <!-- start:row -->
            <div class="col-sm-12">
                <?= $form->field($model, 'email', ['showLabels'=>false])->widget(MaskedInput::className(), [  'clientOptions' => ['alias' => 'email',], 'options' => ['class' => 'form-control', 'placeholder' => $model->getAttributeLabel("email").' ...', 'title' => $model->getAttributeLabel("email"),]]) ?>
            </div>
        </div><!-- end:row  -->

        <div class="row"> <!-- start:row -->
            <?php DynamicFormWidget::begin([
                'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
                'widgetBody' => '.container-items', // required: css class selector
                'widgetItem' => '.item', // required: css class
                'limit' => 10, // the maximum times, an element can be cloned (default 999)
                'min' => 1, // 0 or 1 (default 1)
                'insertButton' => '.add-item', // css class
                'deleteButton' => '.remove-item', // css class
                'model' => $phonesForUser[0],
                'formId' => $model->formName(),
                'formFields' => [
                    'id_phone',
                    'type_phone',
                    'status_phone',
                    'phone_reference',
                    'default_phone',
                ],
            ]); ?>

            <div class="panel-body">
                <div class="panel <?= $model->isNewRecord ? 'panel-success' : 'panel-info';?>" >
                    <div class="panel-heading">
                        <i class="fa fa-phone-square"></i> <?=\Yii::t('app', 'Phone numbers') ?>
                        <?= Html::submitButton( Icon::show('plus', ['class' => 'fa-lg']), ['name' => 'addRow', 'title' => \Yii::t('app', 'Add «{attribute}»',['attribute' => \Yii::t('app', 'Phone')]), 'value' => 'true', 'class' => 'add-item btn btn-success btn-xs pull-right']) ?>
                    </div>
                    <div class="panel-body container-items"><!-- start:panel-body for tangibles-new-data -->

                        <?php foreach ($phonesForUser as $index => $phone) : ?>

                            <div class="item panel panel-default"><!-- start:item for tangibles-new-data -->

                                <div class="panel-body">
                                    <?php
                                    // necessary for update action.
                                    if (!$phone->isNewRecord) {
                                        echo Html::activeHiddenInput($phone, "[{$index}]id");
                                    }
                                    ?>

                                    <div class="row"> <!-- .row -->

                                        <div class="col-sm-3">
                                            <!--    Select search type_phone-->
                                            <?= $form->field($phone, "[{$index}]type_phone", ['showLabels'=>false])->widget(
                                                Select2::classname(), [
                                                'data' => $phone->getTypeList(),
                                                'hideSearch' => true,
                                                'options' => [
                                                    'placeholder'   => $phone->getAttributeLabel("type_phone").' ...',
                                                    'title'         => $phone->getAttributeLabel("type_phone"),
                                                ],
                                                'pluginOptions' => [
//                                            'allowClear' => true
                                                ],
                                            ])->label(false); ?>
                                        </div>

                                        <div class="col-sm-3">
                                            <!--    Select search status_phone-->
                                            <?= $form->field($phone, "[{$index}]status_phone", ['showLabels'=>false])->widget(
                                                Select2::classname(), [
                                                'data' =>$phone->getStatusList(),
                                                'hideSearch' => true,
                                                'options' =>
                                                    [
                                                        'class'=>'status_phone_id',
                                                        'placeholder'   => $phone->getAttributeLabel("status_phone").' ...',
                                                        'title'         => $phone->getAttributeLabel("status_phone"),
                                                    ],
                                                'pluginOptions' => [
//                                            'allowClear' => true
                                                ],
                                            ])->label(false); ?>
                                        </div>

                                        <div class="col-sm-3">
                                            <!--    Select search phone_reference-->
                                            <?= $form->field($phone, "[{$index}]phone_reference", ['showLabels'=>false])->widget(MaskedInput::className(), ['mask' => '+7(999) 999-99-99', 'clientOptions' => [ 'removeMaskOnSubmit' => true, ], 'options' => ['class' => 'form-control', 'placeholder' => $phone->getAttributeLabel("phone_reference").' ...',]])->label(false)  ?>
                                        </div>

                                        <div class="col-sm-2">
                                            <!--    Select search phone_reference-->
                                            <?=  $form->field($phone, "[{$index}]default_phone") ->checkBox([ 'class' => 'default_phone_check', 'label' => '', 'title' => \Yii::t('app', 'Basic phone')], false)->label(\Yii::t('app', 'Basic')); ?>
                                        </div>

                                        <div class="col-sm-1">
                                            <!--    Select button remove-->
                                            <?= Html::button(Icon::show('minus', ['class' => 'fa']), ['class' => 'remove-item btn btn-danger btn-xs', 'data-target' => "receipt-detail-".$index, 'title' => \Yii::t('app', 'Remove this item')]) ?>
                                        </div>

                                    </div><!-- end:row -->
                                </div>
                            </div><!-- end:item for tangibles-new-data -->
                        <?php endforeach; ?>
                    </div><!-- end:panel-body for tangibles-new-data -->
                </div>
            </div>
            <?php DynamicFormWidget::end(); ?>
        </div><!-- end:row  -->

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

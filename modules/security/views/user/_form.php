<?php

/* @var $this yii\web\View */
/* @var $model app\modules\security\models\User */
/* @var $form yii\widgets\ActiveForm */
/* @var array $items */
/* @var array $role */

use yii\web\JsExpression;

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\MaskedInput;

use kartik\widgets\ActiveForm;
use kartik\icons\Icon;

use kartik\select2\Select2;
use kartik\checkbox\CheckboxX;

use igor162\MultiSelect\MultiSelect;
use app\modules\security\models\AuthItem;

$tag_add = \Yii::t('app', 'Add').': '; // Текст добавления для новой "Группы пользователя"
?>

<div class="panel-body"> <!-- start:panel-body-->
    <?php $form = ActiveForm::begin([
        'id' => $model->formName(),
        'enableClientValidation' => (Yii::$app->request->get('form') === $model::FORM_TYPE_AJAX) ? false : true,
        'enableAjaxValidation' => (Yii::$app->request->get('form') === $model::FORM_TYPE_AJAX) ? true : false,
    ]); ?>
    <div class="row"> <!-- start:row -->
        <div class="col-sm-12">
    <?= $form->field($model, 'username')->textInput(['placeholder'=>$model->getAttributeLabel('username'), 'maxlength' => true]) ?>
        </div>
    </div><!-- end:row  -->

    <?php if($model->isNewRecord){ // показывать элемент формы, только в форме создания ?>
        <div class="row"> <!-- start:row -->
            <div class="col-sm-12">
                <?= $form->field($model, 'password')->passwordInput(['placeholder'=>$model->getAttributeLabel('password')]) ?>
            </div>
        </div><!-- end:row  -->
    <div class="row"> <!-- start:row -->
        <div class="col-sm-12">
            <?= $form->field($model, 'password_repeat')->passwordInput(['placeholder'=>$model->getAttributeLabel('password_repeat')]) ?>
        </div>
    </div><!-- end:row  -->
    <?php } ?>

    <div class="row"> <!-- start:row -->
        <div class="col-sm-12">
            <?= $form->field($model,  "status_system")->widget( Select2::classname(), [ 'data' => $model->statusSystemList, 'hideSearch' => true,]); ?>
        </div>
    </div><!-- end:row  -->
    <div class="row"> <!-- start:row -->
        <div class="col-sm-12">
    <?= $form->field($model, 'email')->widget(MaskedInput::className(), [ 'clientOptions' => ['alias' => 'email',], 'options' => ['class' => 'form-control', 'placeholder' => $model->getAttributeLabel("email").' ...',] ]) ?>
        </div>
    </div><!-- end:row  -->

    <?php if($model->isNewRecord){ // показывать элемент формы, только в форме создания ?>
    <div class="row"> <!-- start:row -->
        <div class="col-sm-12">
            <?= $form->field( $model, "send_mail",[ 'showLabels'=>false, 'options'=>[ 'class'=>'form-group has-warning' ] ] )->widget(
                CheckboxX::classname(),
                [
                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                    'autoLabel' => true,
                    'options'=>
                        [
                            'value'=>$model->send_mail,
                            'title' => $model->getAttributeLabel("send_mail").' ...',
                        ],
                    'labelSettings' => [
                        'position' => CheckboxX::LABEL_LEFT
                    ],
                    'pluginOptions' => [
                        'useNative' => false,
                        'enclosedLabel' => true,
                        'threeState' => false,
                    ],
                ]);
            ?>
        </div>
    </div><!-- end:row  -->
    <?php } ?>

    <?php if(Yii::$app->user->can(AuthItem::ROLE_Admin)){ // показывать элемент формы, только "Администраторам" ?>
    <div class="row"> <!-- start:row -->
        <div class="col-sm-12">
            <?= $form->field($model, 'role')->widget(MultiSelect::className(), [
                'label' => $model->getAttributeLabel('role'),
                'items' => $items,
                'selectedItems' => $role,
                'ajax' => false,
            ])->label(false) ?>
        </div>
    </div><!-- end:row  -->
    <?php } ?>

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

</div>  <!-- .panel-body for tangibles-new-dock-->
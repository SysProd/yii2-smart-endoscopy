<?php

use yii\web\JsExpression;

use yii\helpers\Html;
use yii\helpers\Url;

use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;

use kartik\widgets\DatePicker;
use kartik\select2\Select2;

use app\modules\security\models\AuthItem;

/* @var $this yii\web\View */
/* @var $model app\modules\security\models\search\UserSearch */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="modalContent-shops-stuff-search">

    <?php $form = ActiveForm::begin([
        'id' => $model->formName(),
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="row">
        <div class="col-lg-6">
            <?= $form->field($model, 'id_from')->widget(MaskedInput::className(), ['mask' => '9', 'clientOptions' => ['repeat' => 10, 'greedy' => false],]) ?>
        </div>
        <div class="col-lg-6">
            <?= $form->field($model, 'id_till')->widget(MaskedInput::className(), ['mask' => '9', 'clientOptions' => ['repeat' => 10, 'greedy' => false],]) ?>
        </div>
    </div>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <?php if (Yii::$app->user->can(AuthItem::ROLE_Admin)) { // показывать элемент формы, только "Администраторам" ?>
        <!--        --><? /*= $form->field($model, 'group_by')->widget(Select2::classname(), [
//            'data' => $model->userGroupsLists,
            'initValueText' => empty($model->group_by) ? '' : $model->groupBy->userGroupsList, // set the initial display text
            'pluginOptions' => [
                'allowClear' => false,
                'minimumInputLength' => 3,
                'ajax' => [
                    'url' => Url::to(['/security/user-group/user-group-lists']),
                    'type' => 'POST',
                    'dataType' => 'json',
                    'delay' => 250,
                    'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                    'cache' => false,
                ],
            ],
        ]); */ ?>
        <?= $form->field($model, 'group_by')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
    <?php } else { ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
    <?php } ?>

    <?= $form->field($model, 'status_system')->widget(
        Select2::classname(), [
        'data' => $model->statusSystemList,
        'hideSearch' => true,
        'options' => [
            'id' => 'status_system',
            'placeholder' => ' ...',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ]
    ]); ?>

<!--    --><?//= $form->field($model, 'created_by')->widget(
//        Select2::classname(), [
//        'data' => $model->usersList,
//        'hideSearch' => true,
//        'options' => [
//            'placeholder' => ' ...',
//        ],
//        'pluginOptions' => [
//            'changeOnReset' => false,
//            'allowClear' => true,
//            'minimumInputLength' => 3,
//        ],
//    ]) ?>

    <?= $form->field($model, 'created_by')->textInput(['maxlength' => true]) ?>

    <?php if (Yii::$app->user->can(AuthItem::ROLE_Admin)) { // показывать элемент формы, только "Администраторам" ?>

        <?= $form->field($model, 'updated_by')->textInput(['maxlength' => true]) ?>

<!--        --><?//= $form->field($model, 'updated_by')->widget(
//            Select2::classname(), [
//            'data' => $model->usersList,
//            'hideSearch' => true,
//            'options' => [
//                'placeholder' => ' ...',
//            ],
//            'pluginOptions' => [
//                'allowClear' => true,
//                'minimumInputLength' => 3,
//            ],
//        ]) ?>

    <?php } ?>

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

    <?php if (Yii::$app->user->can(AuthItem::ROLE_Admin)) { // показывать элемент формы, только "Администраторам" ?>
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
    <?php } ?>

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

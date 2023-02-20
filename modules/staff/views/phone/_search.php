<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\data\models\search\PhoneSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="phone-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id_phone') ?>

    <?= $form->field($model, 'counterparty_id') ?>

    <?= $form->field($model, 'user_id') ?>

    <?= $form->field($model, 'type_phone') ?>

    <?= $form->field($model, 'status_phone') ?>

    <?php // echo $form->field($model, 'default_phone') ?>

    <?php // echo $form->field($model, 'phone_reference') ?>

    <?php // echo $form->field($model, 'phone_template') ?>

    <?php // echo $form->field($model, 'comment') ?>

    <?php // echo $form->field($model, 'status_system') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
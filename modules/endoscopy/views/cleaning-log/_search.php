<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\endoscopy\models\search\CleaningLogSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cleaning-log-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'add_data') ?>

    <?= $form->field($model, 'tools_by') ?>

    <?= $form->field($model, 'test_tightness_by') ?>

    <?= $form->field($model, 'cleaning_agents_by') ?>

    <?php // echo $form->field($model, 'cleaning_start') ?>

    <?php // echo $form->field($model, 'cleaning_end') ?>

    <?php // echo $form->field($model, 'test_qualities_cleaning_date') ?>

    <?php // echo $form->field($model, 'test_qualities_cleaning_status') ?>

    <?php // echo $form->field($model, 'disinfection_type_by') ?>

    <?php // echo $form->field($model, 'disinfection_auto_by') ?>

    <?php // echo $form->field($model, 'disinfection_auto_start') ?>

    <?php // echo $form->field($model, 'disinfection_auto_end') ?>

    <?php // echo $form->field($model, 'disinfection_manual_by') ?>

    <?php // echo $form->field($model, 'disinfection_manual_start') ?>

    <?php // echo $form->field($model, 'disinfection_manual_end') ?>

    <?php // echo $form->field($model, 'cleaning_tools_start') ?>

    <?php // echo $form->field($model, 'cleaning_tools_end') ?>

    <?php // echo $form->field($model, 'staff_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

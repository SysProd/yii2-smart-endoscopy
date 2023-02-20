<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\endoscopy\models\search\CleaningLevelLogSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cleaning-level-log-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'level_1_add') ?>

    <?= $form->field($model, 'level_2_test_1') ?>

    <?= $form->field($model, 'level_3_clear_1') ?>

    <?= $form->field($model, 'level_4_test_clear_2') ?>

    <?php // echo $form->field($model, 'level_5_disinfection_manual') ?>

    <?php // echo $form->field($model, 'level_5_disinfection_auto') ?>

    <?php // echo $form->field($model, 'level_6_cleaning_tools') ?>

    <?php // echo $form->field($model, 'comment_history') ?>

    <?php // echo $form->field($model, 'staff_by') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

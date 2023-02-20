<?php

use yii\helpers\Html;
use igor162\adminlte\widgets\Box;
use kartik\widgets\AlertBlock;

/* @var $this yii\web\View */
/* @var $model app\modules\endoscopy\models\CleaningLog */

/** Вывод Title */
/** Редактирование найденной модели */
if (isset($model->id_disguise)) {
    $label = Yii::t('app', 'Editing of «{attribute}»', ['attribute' => Yii::t('app', 'Cleaning Logs')]);
    $small = Yii::t('app', '(No. {item})', ['item' => $model->id]);
} else {
    $label = Yii::t('app', 'Adding of «{attribute}»', ['attribute' => Yii::t('app', 'Cleaning Logs')]);
    $small = false;
}

$this->title = [
    'label' => $label,
    'small' => $small,
];

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Cleaning Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rfid-tags-update">

    <?= AlertBlock::widget([
        'useSessionFlash' => true,
        'type' => AlertBlock::TYPE_GROWL,
    ]);
    ?>

    <?php Box::begin([
        'type' => Box::TYPE_PRIMARY,
        'title' => Yii::t('app', 'Complete data'),
        'footer' => false
    ]); ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

    <?php Box::end(); ?>

</div>

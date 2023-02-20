<?php

use yii\helpers\Html;
use igor162\adminlte\widgets\Box;
use kartik\widgets\AlertBlock;
use kartik\icons\Icon;
/* @var $this yii\web\View */
/* @var $model app\modules\staff\models\Staff */
/* @var $phonesForUser app\modules\staff\models\Phone[] */

if (isset($model->id)) { // Редактирование "Сотрудника"
    $label = Yii::t('app', 'Change data of «{attribute}»', ['attribute' => Yii::t('app', 'employee')]);
    $small = Yii::t('app', '(No. {item})', ['item' => $model->id]);
} else { // Добавление "Сотрудника"
    $label = Yii::t('app', 'Adding of «{attribute}»', ['attribute' => Yii::t('app', 'employee')]);
    $small = false;
}
/** Вывод Tittle */
$this->title = [
    'label' => $label,
    'small' => $small,
];

//$this->title = \Yii::t('app', 'Change data of «{attribute}» #{item}', ['attribute' => \Yii::t('app', 'employee'), 'item' => $model->id]);
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'User List'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Change data of «{attribute}» #{item}', ['attribute' => \Yii::t('app', 'stafF'), 'item' => $model->id])];



//Icon::show('get-pocket', ['class' => 'fa-lg'])
?>
<div class="staff-update">

    <?= AlertBlock::widget([
        'useSessionFlash' => true,
        'type' => AlertBlock::TYPE_GROWL,
    ]);
    ?>

    <?php Box::begin([
        'type' => Box::TYPE_PRIMARY,
        'title' => Icon::show('get-pocket', ['class' => 'fa-lg']) . Yii::t('app', 'Complete data'),
        'footer' => false
    ]); ?>

    <?= $this->render('_form', [
        'model' => $model,
        'phonesForUser' => $phonesForUser,
    ]) ?>

    <?php Box::end(); ?>

</div>



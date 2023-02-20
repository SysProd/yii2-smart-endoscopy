<?php

use yii\helpers\Html;
use igor162\adminlte\widgets\Box;
use kartik\widgets\AlertBlock;

/* @var $this yii\web\View */
/* @var $model app\modules\security\models\User */
/* @var array $items */
/* @var array $role */

$this->title = \Yii::t('app', 'Register «{attribute}» in system', ['attribute' => \Yii::t('app', 'user')]);
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'User List'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="User-update">

    <?= AlertBlock::widget([
        'useSessionFlash' => true,
        'type' => AlertBlock::TYPE_GROWL,
    ]);
    ?>

    <?php Box::begin([
        'type' => Box::TYPE_SUCCESS,
        'title' => Yii::t('app', 'Complete data'),
        'footer' => false
    ]); ?>

    <?= $this->render('_form', [
        'model' => $model,
        'items' => $items,
        'role' => $role,
    ]) ?>

    <?php Box::end(); ?>

</div>

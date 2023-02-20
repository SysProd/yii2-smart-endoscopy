<?php
/* @var $this yii\web\View */
/* @var $model app\modules\security\models\AuthItem */
/* @var array $items */
/* @var array $children */

use kartik\widgets\AlertBlock;

$this->title = \Yii::t('app', 'Change data of «{attribute}»', ['attribute' => \Yii::t('app', 'access rule')]);

$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Access rights'), 'url' => ['index']];
$this->params['breadcrumbs'][] = \Yii::t('app', 'Editing');

?>

<div class="auth-item-update pull-left">

    <?php
    echo AlertBlock::widget([
        'useSessionFlash' => true,
        'type' => AlertBlock::TYPE_GROWL,
    ]);
    ?>

    <?= $this->render('_form', [
        'model' => $model,
        'items' => $items,
        'children' => $children,
    ]) ?>

</div>
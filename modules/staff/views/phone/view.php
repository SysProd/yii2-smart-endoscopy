<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\data\models\Phone */

$this->title = $model->id_phone;
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'List of phone'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="phone-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(\Yii::t('app', 'Edit'), ['update', 'id' => $model->id_phone], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(\Yii::t('app', 'Delete'), ['delete', 'id' => $model->id_phone], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => \Yii::t('yii', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id_phone',
            'counterparty_id',
            'user_id',
            'type_phone',
            'status_phone',
            'default_phone',
            'phone_reference',
            'phone_template',
            'comment',
            'status_system',
            'created_by',
            'created_at',
            'updated_by',
            'updated_at',
        ],
    ]) ?>

</div>
<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\endoscopy\models\CleaningLog */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Cleaning Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="cleaning-log-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'add_data',
            'tools_by',
            'test_tightness_by',
            'cleaning_agents_by',
            'cleaning_start',
            'cleaning_end',
            'test_qualities_cleaning_date',
            'test_qualities_cleaning_status',
            'disinfection_type_by',
            'disinfection_auto_by',
            'disinfection_auto_start',
            'disinfection_auto_end',
            'disinfection_manual_by',
            'disinfection_manual_start',
            'disinfection_manual_end',
            'cleaning_tools_start',
            'cleaning_tools_end',
            'staff_by',
            'updated_at',
        ],
    ]) ?>

</div>

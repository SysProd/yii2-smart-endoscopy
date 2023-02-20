<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\data\models\Phone */

$this->title = \Yii::t('app', 'Change data of «{attribute}» #{item}', ['attribute' => \Yii::t('app', 'phone'), 'item' => $model->id_phone]);

$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'List of phone'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => '# ' . $model->id_phone, 'url' => ['view', 'id' => $model->id_phone]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="phone-update">
    <div id="rows container-fluid">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h4>
                    <i class="glyphicon glyphicon-link"></i>
                    <?= Html::encode($this->title) ?>
                </h4>
            </div>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

        </div>
    </div>
</div>

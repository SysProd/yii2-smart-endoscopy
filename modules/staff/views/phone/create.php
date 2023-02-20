<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\data\models\Phone */

$this->title = \Yii::t('app', 'Add «{attribute}»',['attribute' => \Yii::t('app', 'Phone')]);
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'List of phone'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="phone-create">

    <div id="rows container-fluid">
        <div class="panel panel-success">
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

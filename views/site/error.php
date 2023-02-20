<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $statusCode string */
/* @var $exception Exception */

$this->title = $name;
?>
<section class="content">

    <div class="error-page">
        <h2 class="headline text-info"><i class="fa fa-warning text-yellow"></i></h2>

        <div class="error-content">
            <h3 class="headline text-yellow"><?= $statusCode ?> <?= $name ?></h3>

            <p>
                <?= nl2br(Html::encode($message)) ?>
            </p>

            <p>
                <?= \Yii::t('app', 'Please inform us about an error, if you think this is an error on the server side!') ?>
            </p>

            <p>
                <a href='<?= Yii::$app->homeUrl ?>'> <?=\Yii::t('app', 'Go back to main page')?> </a>
            </p>

        </div>
    </div>

</section>

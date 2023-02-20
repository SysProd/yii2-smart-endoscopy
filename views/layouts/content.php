<?php
use yii\widgets\Breadcrumbs;
//use dmstr\widgets\Alert;
use app\widgets\Alert;

?>
<div class="content-wrapper">

    <?php if($this->title !== null){ ?>
    <section class="content-header">
        <?php if (isset($this->blocks['content-header'])) { ?>
            <h1><?= $this->blocks['content-header'] ?></h1>
        <?php } else { ?>
            <h1>
                <?php
                if ($this->title !== null) {
                    if(is_array($this->title)){
                        echo \yii\helpers\Html::encode(\yii\helpers\ArrayHelper::getValue($this->title, 'label'));
                        echo \yii\helpers\Html::tag('small', \yii\helpers\Html::encode(\yii\helpers\ArrayHelper::getValue($this->title, 'small')));
                    }else{
                        echo \yii\helpers\Html::encode($this->title);
                    }
                } else {
                    echo \yii\helpers\Inflector::camel2words(
                        \yii\helpers\Inflector::id2camel($this->context->module->id)
                    );
                    echo ($this->context->module->id !== \Yii::$app->id) ? '<small>Module</small>' : '';
                } ?>
            </h1>
        <?php } ?>

        <?=
        Breadcrumbs::widget(
            [
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]
        ) ?>
    </section>
    <?php } ?>

    <?php if($this->title !== null){ ?>
    <section class="content">
        <?php } ?>

        <?= Alert::widget() ?>
        <?= $content ?>

    <?php if($this->title !== null){ ?>
    </section>
    <?php } ?>

</div>

<footer class="main-footer">
    <strong>Copyright &copy; <?= date('Y') ?> <?=yii\helpers\Html::a(\Yii::$app->name, \Yii::$app->urlManager->createAbsoluteUrl(['site/index']));?>.</strong> All rights reserved.
</footer>

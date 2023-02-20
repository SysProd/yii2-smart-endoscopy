<?php
use yii\helpers\Html;
use app\assets\AdminLteHelper;
use app\assets\AppAsset;
use yii\helpers\ArrayHelper;

/* @var $this \yii\web\View */
/* @var $content string */


if (Yii::$app->controller->action->id === 'login') {
    /**
     * Do not use this code in your template. Remove it.
     * Instead, use the code  $this->layout = '//main-login'; in your controller.
     */
    echo $this->render(
        'main-login',
        ['content' => $content]
    );
} else {

    AppAsset::register($this);
    // путь к библиотеке AdminLTE
    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte');
    ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title>
            <?php
            if (!empty($this->title)) {
                if(is_array($this->title)){
                    echo Html::encode(ArrayHelper::getValue($this->title, 'label'));
                }else{
                    echo Html::encode($this->title);
                }
            }else{
                echo Html::encode(Yii::$app->name);
            }
            ?>
        </title>
        <?php $this->head() ?>
    </head>
    <body class="hold-transition <?= AdminLteHelper::skinClass() ?> sidebar-mini <?= !(Yii::$app->user->can(\app\modules\security\models\AuthItem::ROLE_Admin))? 'sidebar-collapse':''; ?>">
    <?php $this->beginBody() ?>
    <div class="wrapper">

        <?= $this->render(
            'header.php',
            ['directoryAsset' => $directoryAsset]
        ) ?>

        <?= $this->render(
            'left.php',
            ['directoryAsset' => $directoryAsset]
        )
        ?>

        <?= $this->render(
            'content.php',
            ['content' => $content, 'directoryAsset' => $directoryAsset]
        ) ?>

    </div>

    <?php $this->endBody() ?>
    </body>
    </html>
    <?php $this->endPage() ?>
<?php } ?>

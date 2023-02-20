<?php

/**
 * Управление пользователями
 * Список карт
 * Список банков
 * Данные Рассположения
 * Список Шопов
 * Список материалов для маскировки
 * Список поставщиков
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use kartik\nav\NavX;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use kartik\icons\Icon;
use igor162\adminlte\ColorCSS;
use igor162\adminlte\widgets\SidebarMenu;
use app\modules\security\models\AuthItem;

?>
<aside class="main-sidebar">

    <section class="sidebar">
        <?php

        // check active url
        $checkController = function ($array) {
            foreach ($array as $route)
                if ($this->context->getUniqueId() == $route) {
                    return $route;
                }
        };
        //'active' => $checkController(['company', 'company-counterparty' ]),


        $menuItems2 = [
            ['label' => \Yii::t('app', 'System Menu'), 'visible' => Yii::$app->user->can(AuthItem::ROLE_Admin), 'options' => ['class' => 'header']],
            [
                'label' => \Yii::t('app', 'Menu Yii2'),
                'icon' => 'fa fa-bullhorn',
                'url' => '#',
                'visible' => Yii::$app->user->can(AuthItem::ROLE_Admin),
                'items' => [
                    ['label' => 'Gii', 'icon' => 'fa fa-file-code-o', 'url' => ['/gii']],
                    ['label' => 'Debug', 'icon' => 'fa fa-dashboard', 'url' => ['/debug'],],
                ],
            ],
            [
                'label' => \Yii::t('app', 'Security'),
                'icon' => 'fa fa-shield text-danger',
                'url' => '#',
                'active' => $checkController(['security/user', 'security/auth-item']),
                'visible' => Yii::$app->user->can(AuthItem::ROLE_Admin),
                'items' => [
                    ['label' => \Yii::t('app', 'Users'), 'icon' => 'fa fa-universal-access', 'url' => ['/security/user/index'], 'active' => $checkController(['security/user']),],
                    ['label' => \Yii::t('app', 'Access rights'), 'icon' => 'fa fa-lock', 'url' => ['/security/auth-item/index'], 'active' => $checkController(['security/auth-item']), 'visible' => Yii::$app->user->can(AuthItem::ROLE_Admin)],
                ],
            ],

            // Основное меню
            ['label' => \Yii::t('app', 'Endoscope cleaning accounting system'), 'options' => ['class' => 'header'], 'visible' => Yii::$app->user->can(AuthItem::ROLE_Admin),],

            ['label' => \Yii::t('app', 'Tags and links'),   'icon' => 'fa fa-codepen', 'url' => ['/endoscopy/rfid-tags/index'], 'visible' => Yii::$app->user->can(AuthItem::ROLE_Admin),],
            ['label' => \Yii::t('app', 'Machined Tool'),   'icon' => 'fa fa-gears', 'url' => ['/endoscopy/tools/index'], 'visible' => Yii::$app->user->can(AuthItem::ROLE_Admin),],
            ['label' => \Yii::t('app', 'Cleaning Tools'),   'icon' => 'fa fa-shower', 'url' => ['/endoscopy/tools-cleaning-machines/index'], 'visible' => Yii::$app->user->can(AuthItem::ROLE_Admin),],
            ['label' => \Yii::t('app', 'Cleaning Agents'),  'icon' => 'fa fa-flask', 'url' => ['/endoscopy/tools-cleaning-agents/index'], 'visible' => Yii::$app->user->can(AuthItem::ROLE_Admin),],
            ['label' => \Yii::t('app', 'Instrument Statuses'),    'icon' => 'fa fa-bell', 'url' => ['/endoscopy/tools-statuses/index'], 'visible' => Yii::$app->user->can(AuthItem::ROLE_Admin),],
            ['label' => \Yii::t('app', 'Cleaning Levels'),  'icon' => 'fa fa-signal', 'url' => ['/endoscopy/cleaning-level-log/index'], 'visible' => Yii::$app->user->can(AuthItem::ROLE_Admin),],
            ['label' => \Yii::t('app', 'Cleaning Logs'),    'icon' => 'fa fa-sitemap', 'url' => ['/endoscopy/cleaning-log/index']],
            // Управление сотрудниками
            ['label' => \Yii::t('app', 'Employee management'), 'options' => ['class' => 'header'], 'visible' => Yii::$app->user->can(AuthItem::ROLE_Admin),],
            ['label' => \Yii::t('app', 'Staff'),            'icon' => 'fa fa-users text-primary', 'url' => ['/staff/staff/index'], 'visible' => Yii::$app->user->can(AuthItem::ROLE_Admin),],
        ];

        echo SidebarMenu::widget(['items' => $menuItems2,]);
        ?>

    </section>

</aside>

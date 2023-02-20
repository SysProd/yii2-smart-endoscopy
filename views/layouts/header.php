<?php
use yii\helpers\Html;
use kartik\icons\Icon;
use yii\helpers\Url;

use igor162\nav\NavBar;
use igor162\nav\Nav;
use igor162\adminlte\ColorCSS;

/* @var $this \yii\web\View */
/* @var $content string */
?>

<header class="main-header">

    <?php

    NavBar::begin([
        'brandLabel' => Icon::show('heartbeat', ['class' => 'fa-lg']) . Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'brandLabelSm' => Icon::show('heartbeat', ['class' => 'fa-lg']),
    ]);

    /*  echo NavX::widget([
          'encodeLabels' => false,
          'activateParents' => true,
          'dropdownIndicator' => false,
          'options' => ['class' => 'nav navbar-nav'],
          'items' => [
              [
                  'label' => Icon::show('envelope-o').Html::tag('span', 2, ['class' => 'label '.\igor162\adminlte\ColorCSS::BG_FUCHSIA]),
                  'items' => [
                      '
                  <!-- Messages Header -->
                          <li class="header"> You have 4 messages </li>
                  <!-- Messages Body -->
                  <li>
                  <!-- inner menu: contains the actual data -->
                  <div class="slimScrollDiv" style="position: relative; overflow: hidden; width: auto; height: 200px;"><ul class="menu" style="overflow: hidden; width: 100%; height: 200px;">
                    <li><!-- start message -->
                      <a href="#">
                        <div class="pull-left">
                          <img src="'.$directoryAsset.'/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
                        </div>
                        <h4>
                          Support Team
                          <small><i class="fa fa-clock-o"></i> 5 mins</small>
                        </h4>
                        <p>Why not buy a new awesome theme?</p>
                      </a>
                    </li>
                    <!-- end message -->
                    <li>
                      <a href="#">
                        <div class="pull-left">
                          <img src="'.$directoryAsset.'/dist/img/user3-128x128.jpg" class="img-circle" alt="User Image">
                        </div>
                        <h4>
                          AdminLTE Design Team
                          <small><i class="fa fa-clock-o"></i> 2 hours</small>
                        </h4>
                        <p>Why not buy a new awesome theme?</p>
                      </a>
                    </li>
                    <li>
                      <a href="#">
                        <div class="pull-left">
                          <img src="'.$directoryAsset.'/dist/img/user4-128x128.jpg" class="img-circle" alt="User Image">
                        </div>
                        <h4>
                          Developers
                          <small><i class="fa fa-clock-o"></i> Today</small>
                        </h4>
                        <p>Why not buy a new awesome theme?</p>
                      </a>
                    </li>
                    <li>
                      <a href="#">
                        <div class="pull-left">
                          <img src="'.$directoryAsset.'/dist/img/user3-128x128.jpg" class="img-circle" alt="User Image">
                        </div>
                        <h4>
                          Sales Department
                          <small><i class="fa fa-clock-o"></i> Yesterday</small>
                        </h4>
                        <p>Why not buy a new awesome theme?</p>
                      </a>
                    </li>
                    <li>
                      <a href="#">
                        <div class="pull-left">
                          <img src="'.$directoryAsset.'/dist/img/user4-128x128.jpg" class="img-circle" alt="User Image">
                        </div>
                        <h4>
                          Reviewers
                          <small><i class="fa fa-clock-o"></i> 2 days</small>
                        </h4>
                        <p>Why not buy a new awesome theme?</p>
                      </a>
                    </li>
                  </ul>
                  <div class="slimScrollBar" style="background: rgb(0, 0, 0) none repeat scroll 0% 0%; width: 3px; position: absolute; top: 0px; opacity: 0.4; display: block; border-radius: 7px; z-index: 99; right: 1px;"></div>
                  <div class="slimScrollRail" style="width: 3px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(51, 51, 51) none repeat scroll 0% 0%; opacity: 0.2; z-index: 90; right: 1px;"></div>
                  </div>
                </li>
                  <!-- Messages Footer-->
                          <li class="footer">
                          <a href="#">See All Messages</a>
                          </li>'
                  ],
                  'options' => ['class' => 'dropdown messages-menu'],
              ],
          ],
      ]);*/

/*    echo Nav::widget([
        'encodeLabels' => false,
        'activateParents' => true,
        'dropdownIndicator' => Html::img(Url::to('@web/images/male.png'), ['class' => 'user-image', 'alt' => 'User Image']) . Html::tag('span', ''),
        'options' => ['class' => 'nav navbar-nav'],
        'items' => [
            [
                'label' => \Yii::$app->user->identity->fullName,
                'small' => 11,
                'items' => [
                    '
                <!-- Menu Header -->
                <li class="user-header">
                ' . Html::img(Url::to('@web/images/male.png'), ['class' => 'img-circle', 'alt' => 'User Image']) . '
                            <p>
                            ' . \Yii::$app->user->identity->fullName . '<small>' . \Yii::t("app", "Username") . ': #' . \Yii::$app->user->identity->username . '</small>
                            </p>
                        </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                            <div class="pull-left">
                                ' . Html::a(
                        \Yii::t('app', 'Profile'),
                        ['/user/profile'],
                        ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                    ) . '
                            </div>
                            <div class="pull-right">
                                ' . Html::a(
                        \Yii::t('app', 'Log out'),
                        ['/site/logout'],
                        ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                    ) . '
                            </div>
                        </li>'
                ],
                'options' => ['class' => 'dropdown user user-menu'],
            ],
        ],

    ]);*/

    if (!Yii::$app->user->isGuest) {

        $group_by = isset(\Yii::$app->user->identity->group_by) ? 'Группа:: #'.\Yii::$app->user->identity->groupBy->id.' ('.\Yii::$app->user->identity->groupBy->name.')' : '';
        $roleNameString = \Yii::$app->user->identity->getRoleNameString(1);
        $roleNameString = isset($roleNameString) ? '<br> Роль пользователя:: {'.$roleNameString.'}' : '';
        $gender = isset(\Yii::$app->user->identity->userProfile->gender) ? \Yii::$app->user->identity->userProfile->gender : 'Male';

        echo igor162\nav\NavbarUser::widget([
            'userName' => \Yii::$app->user->identity->username,
            'userGender' => $gender,
//        'dataHeaderSmall' => \Yii::t('app', 'Created: {attribute}', ['attribute' => date("d-m-Y H:m",\Yii::$app->user->identity->created_at)]),
            'dataHeaderSmall' => $group_by . $roleNameString,
            'panelFooter' =>
                [
                    'labelProfile' => \Yii::t('app', 'Profile'),
                    'linkProfile' => Url::to(['/security/profile/update', 'id' => \Yii::$app->user->identity->id]),
                    'classProfile' => "btn btn-default btn-flat",

                    'labelSignOut' => \Yii::t('app', 'Log out'),
                    'linkSignOut' => Url::to('/logout'),
                    'classSignOut' => 'btn btn-default btn-flat'
                ]
        ]);

    }

    NavBar::end();

    ?>

</header>
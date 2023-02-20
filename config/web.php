<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'Smart Hospital',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [
        // Мой модуль
        'api' => [
            'class' => '\app\modules\api\ApiModules',
        ],
        'staff' => [
            'class' => '\app\modules\staff\StaffModule',
        ],
        'security' => [
            'class' => '\app\modules\security\SecurityModule',
        ],
        'endoscopy' => [
            'class' => '\app\modules\endoscopy\EndoscopyModule',
        ],

        'dynagrid' => [
            'class' => '\igor162\dynagrid\Module',
            'dbSettings' => [
                'tableName' => 'dynagrid',
                'idAttr' => 'id',
//                'filterAttr' => 'filter_id',
//                'sortAttr' => 'sort_id',
//                'dataAttr' => 'sort_id',
            ],
            'dbSettingsDtl' => [
                'tableName' => 'dynagrid_dtl',
                'idAttr' => 'id',
                'categoryAttr' => 'category',
                'nameAttr' => 'name',
                'dataAttr' => 'data',
                'dynaGridId' => 'dynagrid_id',
            ],
            'i18n' => [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@kvdynagrid/messages',
                'forceTranslation' => true
            ],
            // other settings (refer documentation)
        ],
        'gridview' => [
            'class' => '\kartik\grid\Module',
            // enter optional module parameters below - only if you need to
            // use your own export download action or custom translation
            // message source
//             'downloadAction' => 'gridview/export/download',
//             'i18n' => [
//            'class' => 'yii\i18n\PhpMessageSource',
//            'basePath' => '@kvgrid/messages',
//            'forceTranslation' => true
//                    ]
        ],
    ],
    'language'          =>  'ru',    // исходный язык для пользователя
    'sourceLanguage'    =>  'en',    // исходный язык, на котором изначально написаны фразы в приложении
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [

        // Подлючение Admin LTE
        /*        'view' => [
                    'theme' => [
                        'pathMap' => [
                            '@app/views' => '@vendor/dmstr/yii2-adminlte-asset/example-views/yiisoft/yii2-app'
        //                    '@app/views' => '@vendor/angulr/yii2-adminangulr-asset/yiisoft/yii2-app'
                        ],
                    ],
                ],*/
        // Стиль темы Admin LTE
        'assetManager' => [
            'bundles' => [
                'app\assets\AppAsset' => [
                    'skin' => 'skin-blue',
                ],
            ],
        ],
//        rbac или роли доступа
        'authManager'=>
            [
                'class' => 'yii\rbac\DbManager',
//                'cache' => 'cache', //Включаем кеширование (включить когда будет стабильно работать приложение)
                'defaultRoles' => ['guest'],
            ],
        'request' => [
            'enableCookieValidation' => true,
            'enableCsrfValidation' => true, // проверка CSRF токена
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'jub3hpOOleU7SjuuGH38ocIahQzfEGk5',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'i18n' => [             // компонент мультизязычности
            'translations' => [
                'app' => [
                    'class' => 'yii\i18n\PhpMessageSource',
//                    'forceTranslation' => true,
                    'basePath' => '@app/messages',  // каталог, где будут располагаться словари
                    'fileMap' => [
                        'app'       => 'app.php',   // группа фраз и её файл-источник
                        'yii'       => 'yii.php',   // для перевода приложение
                        'app/error' => 'error.php', // для ошибок (тоже какое-то подмножетсво переводимых фраз)
                    ],
                ]
            ],
        ],
//        настройки форматирования
        'formatter' => [
//            'class' => 'yii\i18n\Formatter',
            'class' => 'app\widgets\actions\Formatter',
            'locale' => 'ru',
            'defaultTimeZone' => 'Europe/Minsk',
            'timeZone' => 'Europe/Minsk',
            'dateFormat' => 'php:d-M-Y',
            'datetimeFormat' => 'php:d-M-Y H:i:s',
            'timeFormat' => 'php:H:i:s',
            'thousandSeparator' => ' ',
            'decimalSeparator' => '.',
//            'nullDisplay'=>'Не задано',
        ],
        'user' => [
//            'identityClass' => 'app\models\User',
//            'enableAutoLogin' => true,

            'class' => 'yii\web\User',
            'identityClass' => 'app\modules\security\models\User',
            'enableAutoLogin' => true,
            'enableSession' => true,
            'autoRenewCookie' => true,
//            'loginUrl' => ['site/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true, // включить красивые ссылки
//            'enableStrictParsing' => true, // true - использовать шаблоны и маршруты указанных в "rule" без исключения
            'showScriptName' => false, // скрывать в ссылке index.php
            'rules' => [
                [
                    'pattern'   => '',          // Шаблон ссылки
                    'route'     => 'site/index',// Маршрут для ссылки
                    'suffix'    => '',          // Суффикс, подставляемый после шаблона ссылки
                ],
                [
                    'pattern'   => '<action:(index|login|logout)>',
                    'route'     => 'site/<action>',
                    'suffix'    => '',
                ],
                [
                    'pattern'   => '<controller>/<action>/<id:\d+>',
                    'route'     => '<controller>/<action>',
                    'suffix'    => '',
                ],
                [
                    'pattern'   => '<controller>/<action>',
                    'route'     => '<controller>/<action>',
                    'suffix'    => '.html',
                ],
                [
                    'pattern'   => 'profile/<id:\d+>',
                    'route'     => 'security/profile/update',
                    'suffix'    => '',
                ],
                [
                    'pattern'   => '<module>/<controller>/<action>/<id:\d+>',
                    'route'     => '<module>/<controller>/<action>',
                    'suffix'    => '',
                ],
                [
                    'pattern'   => '<module>/<controller>/<action>',
                    'route'     => '<module>/<controller>/<action>',
                    'suffix'    => '.html',
                ],
            ],
        ],
    ],
    //  ### Настройка доступа к страницам, только авторизованным пользователям ###
    'as beforeRequest' => [
        'class' => yii\filters\AccessControl::className(),
        'except' => ['login'],
        'rules' => [
            [
                'actions' => ['login', 'error', 'add-key'],
                'allow' => true,
                'roles' => ['?'],   // гость
//                'ips' => ['192.168.0.*','127.0.0.*'], // авторизация по ip
            ],
            [
//                'actions' => ['logout', 'index', 'error'],
                'allow' => true,
                'roles' => ['@'],   // авторизованные пользователь
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => [$_SERVER['REMOTE_ADDR'], '127.0.0.1', '192.168.0.43', '192.168.0.*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => [$_SERVER['REMOTE_ADDR'], '127.0.0.1', '192.168.0.43', '192.168.0.*'],
    ];
}

return $config;

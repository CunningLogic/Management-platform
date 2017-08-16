<?php


$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules'   => [
        'live'  => [
            'class' => 'app\modules\live\Module',
         ],
    ],
    'components' => [
/*        'session' => [
            'class' => 'yii\web\DbSession',
            'db' => 'db_iuav',  // 数据库连接的应用组件ID，默认为'db'.
            'sessionTable' => 'session', // session 数据表名，默认为'session'.
        ],*/

        'request' => [
            'enableCookieValidation' => false,
            //'cookieValidationKey' => 'SdE_sbIf9OyOW43VEjO7olrtk6LM72rG',
        ],
        'view' => [
            'renderers' => [
                'tpl' => [
                    'class' => 'yii\smarty\ViewRenderer',
                    //'cachePath' => '@runtime/Smarty/cache',
                ],
            ],
        ],
       /*
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        */
        'cache' => isset($YII_GLOBAL['SEVERCACHE']) ? $YII_GLOBAL['SEVERCACHE'] : ['class' => 'yii\caching\FileCache'],

        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'djiuser' => [
             'class' => 'app\components\DjiUser',
             'returnUrl'      => isset($YII_GLOBAL['djiuser']['returnUrl']) ? $YII_GLOBAL['djiuser']['returnUrl'] : '',
             'loginUrl'       => isset($YII_GLOBAL['djiuser']['loginUrl']) ? $YII_GLOBAL['djiuser']['loginUrl'] : '',
             'ssoConfig'      =>  [
                 'apiDomain'        => isset($YII_GLOBAL['djiuser']['ssoConfig']['apiDomain']) ? $YII_GLOBAL['djiuser']['ssoConfig']['apiDomain'] : '',
                 'cookieName'       => '_meta_key',
                 'getKeyName'       => 'key',
                 'getTokenUrl'      => '/accounts/get_token_by_meta_key',
                 'getUserInfoUrl'   => '/accounts/get_account_info_by_key',
                 'cookieDomain'     => isset($YII_GLOBAL['djiuser']['ssoConfig']['cookieDomain']) ? $YII_GLOBAL['djiuser']['ssoConfig']['cookieDomain'] : '',
                 'appid'            => isset($YII_GLOBAL['djiuser']['ssoConfig']['appid']) ? $YII_GLOBAL['djiuser']['ssoConfig']['appid'] : '',
                 'appkey'           => isset($YII_GLOBAL['djiuser']['ssoConfig']['appkey']) ? $YII_GLOBAL['djiuser']['ssoConfig']['appkey'] : '',
             ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => isset($YII_GLOBAL['mailer']['transport']['host']) ? $YII_GLOBAL['mailer']['transport']['host'] : '',
                'username' => isset($YII_GLOBAL['mailer']['transport']['username']) ? $YII_GLOBAL['mailer']['transport']['username'] : '',
                'password' => isset($YII_GLOBAL['mailer']['transport']['password']) ? $YII_GLOBAL['mailer']['transport']['password'] : '',
                'port' => isset($YII_GLOBAL['mailer']['transport']['port']) ? $YII_GLOBAL['mailer']['transport']['port'] : 25,
                'encryption' => isset($YII_GLOBAL['mailer']['transport']['encryption']) ? $YII_GLOBAL['mailer']['transport']['encryption'] : 'tls',
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => isset($YII_GLOBAL['mailer']['messageConfig']['from']) ? $YII_GLOBAL['mailer']['messageConfig']['from'] : [],
            ],
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
        'db' => require(__DIR__ . '/db.php'),

        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => isset($YII_GLOBAL['REDIS']['hostname']) ? $YII_GLOBAL['REDIS']['hostname'] : 'localhost',
            'port' => isset($YII_GLOBAL['REDIS']['port'] ) ? $YII_GLOBAL['REDIS']['port']  : '6379',
            'database' => 0,
            'password' => isset($YII_GLOBAL['REDIS']['password'] ) ? $YII_GLOBAL['REDIS']['password']  : null,
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            //'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [               
                '<country:\w+>/adminagent<_c:(\/?)>'=>'adminagent', 
                '<country:\w+>/adminagent/<_c:\S+>'=>'adminagent/<_c>', 
                

            ],
        ]

    ],
    'params' => $params,
];
//if (1) {
if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;

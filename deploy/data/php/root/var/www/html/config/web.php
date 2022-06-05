<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['monolog', 'log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'tfo0yXUd6BEeDqqfcO7HBRTalaRLIhSZ',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'monolog' => [
            'class' => Beter\Yii2BeterLogging\MonologComponent::class,
            'channels' => [
                'main' => [
                    [
                        'name' => 'standard_stream',
                        'stream' => 'php://stderr',
                        'level' => 'debug',
                        'bubble' => true,
                        'formatter' => [
                            'name' => 'console',
                            'colorize' => true,
                            'indentSize' => 4,
                            'trace_depth' => 10,
                        ]
                    ],
                ],
                'processor' => [
                    [
                        'name' => 'basic_processor',
                        'env' => YII_ENV, // dev, prod, etc
                        'app' => 'myapp',
                        'service' => 'api',
                        'host' => gethostname(), // or set it as you want
                    ]
                ],
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'flushInterval' => 1,
            'targets'       => [
                'monolog-proxy'      => [
                    'class'          => Beter\Yii2BeterLogging\ProxyLogTarget::class,
                    'targetLogComponent' => [
                        'componentName' => 'monolog',
                        'logChannel' => 'main'
                    ],
                    'categories' => [],
                    'except' => [],
                    'exportInterval' => 1,
                    'levels'         => [
                        'error',
                        'warning',
                        'info',
                        'trace',
                    ],
                ],
                'file-target' => [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ]
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    // array_unshift($config['bootstrap'], 'debug');
    $config['bootstrap'][] = 'debug';

    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['*'],
    ];
}

return $config;

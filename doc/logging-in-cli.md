# yii2-beter-logging: Logging in CLI

## The case

Many modern applications ship web services, cronjobs and workers.

Each of them requires its own logging pipeline and can't be used with default settings. Imagine you need to have

* script for manual launch, like database migration script;
* script to make some cleaning (cronjob script)

The difference between them consists in the fact that you are obliged to have different settings. Definitely you don't
need to send logs to logstash when you run manual migration, because you want to see pretty printed errors in your
console. From the other hand, you don't want to read log files of cronjobs, I believe you want to use logstash for
that type of jobs. Even if you are a fan of "12-factor app", and you want to deliver log entries in the JSON format,
you need different setup.

Sometimes it can be solved by creation of few environments and separation of application. Part of described issues
will follow you anyway.

But `yii2-beter-logging` makes your life easier and solve described issues.

## Env Settings

To implement `yii2-beter-logging` approach just use `Beter\Yii2BeterLogging\EnvVarSettings` class. This class contains
a set of helpers that checks variables in the `$_SERVER` and checks variables set up as an environment variables.

> `$_SERVER` super globals may contain variables passed by nginx, apache or any other webserver. As an example,
> for php-fpm they are set with `php_flag`, `php_admin_flag`, `php_value`, `php_admin_value`.
> [Check the doc](https://www.php.net/manual/en/install.fpm.configuration.php).

Here is a sample script that demonstrates this approach for a specific script

```php
<?php

use Beter\Yii2BeterLogging\EnvVarSettings;

$useColors = EnvVarSettings::colorSettingEnabled() ?? true;
$machineReadableFormat = EnvVarSettings::machineReadableSettingEnabled() ?? false;
$logstashEnabled = EnvVarSettings::logstashSettingEnabled() ?? false;
```

* `EnvVarSettings::colorSettingEnabled()` checks `NO_COLOR` env setting;
* `EnvVarSettings::machineReadableSettingEnabled()` checks `LOGGER_MACHINE_READABLE` env setting;
* `EnvVarSettings::logstashSettingEnabled()` checks `LOGGER_ENABLE_LOGSTASH` env setting;

`EnvVarSettings::colorSettingEnabled()` follows https://no-color.org/ approach and can't be redefined.

All other methods may be called with a parameter that specifies env name to check, for example,
`EnvVarSettings::logstashSettingEnabled('LOGSTASH')` will check `$_SERVER['LOGSTASH']` and will call
`getenv('LOGSTASH')`.

Every method returns `null` of no env setting was detected at all, returns `true` if env setting is on of the strings
`True`, `true`, `True` or `1`. Methods return `false` on all other scenarios.

```bash
$ php script.php // EnvVarSettings::colorSettingEnabled() === null
$ NO_COLOR=1 php script.php // EnvVarSettings::colorSettingEnabled() === true
$ NO_COLOR=True php script.php // EnvVarSettings::colorSettingEnabled() === true
$ NO_COLOR=0 php script.php // EnvVarSettings::colorSettingEnabled() === false
$ NO_COLOR=anythingelse php script.php // EnvVarSettings::colorSettingEnabled() === false

$ LOGGER_ENABLE_LOGSTASH=1 php scrip.php // EnvVarSettings::logstashSettingEnabled() === true
$ LOGSTASH=1 php scrip.php // EnvVarSettings::logstashSettingEnabled() === false
$ LOGSTASH=1 php scrip.php // EnvVarSettings::logstashSettingEnabled('LOGSTASH') === false
```

> It's possible to play with [examples in a demo container](development-and-testing.md).

## Screenshots

Colorized stderr output

![CLI colors](https://raw.githubusercontent.com/BETER-CO/yii2-beter-logging/master/doc/assets/cli_colors.jpg)

`NO_COLOR=1` or `colorize == false` in the `console` formatter.

![CLI NO_COLOR](https://raw.githubusercontent.com/BETER-CO/yii2-beter-logging/master/doc/assets/cli_no_color.jpg)

`LOGGER_MACHINE_READABLE=1` or `logstash` formatter for the `standard_stream` handler.

![CLI LOGGER_MACHINE_READABLE](https://raw.githubusercontent.com/BETER-CO/yii2-beter-logging/master/doc/assets/cli_machine_readable.jpg)

Structure for the message for the logstash is parsed by the logstash successfully.

![Logstash message](https://raw.githubusercontent.com/BETER-CO/yii2-beter-logging/master/doc/assets/logstash_message.jpg)

Structure for the extension for the logstash parsed by the logstash successfully.

![Logstash exception](https://raw.githubusercontent.com/BETER-CO/yii2-beter-logging/master/doc/assets/logstash_exception.jpg)

## Usage in config files

You may use these methods to implement logic in your config files. Here is an example for `console.php` yii config.

```php
<?php
use Beter\Yii2BeterLogging\MonologComponent;
use Beter\Yii2BeterLogging\ProxyLogTarget;
use Beter\Yii2BeterLogging\EnvVarSettings;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$useColors = EnvVarSettings::colorSettingEnabled() ?? true;
$machineReadableFormat = EnvVarSettings::machineReadableSettingEnabled() ?? false;

if ($machineReadableFormat) {
    $standardStreamFormatter = [
        'name' => 'logstash',
        'trace_depth' => 10,
    ];
} else {
    $standardStreamFormatter = [
        'name' => 'console',
        'colorize' => $useColors,
        'indentSize' => 4,
        'trace_depth' => 10,
    ];
}


$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['monolog', 'log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'monolog' => [
            'class' => Beter\Yii2BeterLogging\MonologComponent::class,
            'channels' => [
                'main' => [
                    'handler' => [
                        [
                            'name' => 'standard_stream',
                            'stream' => 'php://stderr',
                            'level' => 'debug',
                            'bubble' => true,
                            'formatter' => $standardStreamFormatter,
                        ],
                    ],
                    'processor' => [
                        [
                            'name' => 'basic_processor',
                            'env' => YII_ENV, // dev, prod, etc
                            'app' => 'myapp',
                            'service' => 'cli',
                            'host' => gethostname(), // or set it as you want
                        ]
                    ],
                ],
            ],
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
];

$logstashEnabled = EnvVarSettings::logstashSettingEnabled() ?? false;
if ($logstashEnabled) {
    $logstashHandlerConfig = [
        'name' => 'logstash',
        'label' => 'logstash',
        'level' => 'debug',
        'bubble' => true,
        'host' => '1.2.3.4', // or host.address.com
        'port' => 5044,
        'socket_transport' => 'tcp',
        'persistent' => false,
        'socket_timeout' => 1,
        'writing_timeout' => 1,
        'connection_timeout' => 1,
        'max_handle_errors_before_disabling' => 3,
        'formatter' => [
            'name' => 'logstash',
            'trace_depth' => 10,
        ]
    ];

    // add as a first handler, bubbling
    array_unshift($config['components']['monolog']['channels']['main']['handler'], $logstashHandlerConfig);
}

return $config;

```

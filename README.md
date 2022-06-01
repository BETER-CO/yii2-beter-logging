# yii2-beter-logging

Bulletproof logging for enterprise yii2 projects.

monolog 2.x integration, custom LogTarget with delayed queue, pretty console handler, logstash via udp and tcp with deep yii2 integration.

![CLI colors](https://raw.githubusercontent.com/BETER-CO/yii2-beter-logging/master/doc/assets/cli_colors.jpg)

Features:
* uses monolog under the hood;
* implements custom [log Target](https://www.yiiframework.com/doc/api/2.0/yii-log-target) to pass log entries to monolog;
* allows handlers chaining if logstash handler fails, so no more loses of log entries;
* allows tracking statistics of log handlers;
* doesn't turn off the whole log Target on errors;
* allows switching off handlers if they fail specific amount of times;
* supports stdout/stderr with colors;
* supports logstash tcp and udp transport;
* native support of yii log features.

## Installation

The preferred way to install this extension is through [composer](https://getcomposer.org/).

Either run

```
composer require beter/yii2-beter-loging:"~1.0.0"
```

or add

```
"beter/yii2-beter-loging": "~1.0.0"
```

to the require section of your composer.json.

## Configuration

To use this extension, you have to configure it in your application configuration.

### Configure Yii2 log component

Add `Beter\Yii2BeterLogging\ProxyLogTarget` class to the list of your log Targets

```
'log' => [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'flushInterval' => 1,
    'targets' => [
        // other log targets

        'monolog-proxy' => [
            'class' => Beter\Yii2BeterLogging\ProxyLogTarget::class,
            'targetLogComponent' => [
                'componentName' => 'monolog',
                'logChannel' => 'main'
            ],
            'exportInterval' => 1,
            'categories' => [],
            'except' => [
                'yii\web\UrlManager::parseRequest',
                'yii\web\UrlRule::parseRequest',
            ],
            'levels' => ['error', 'warning', 'info', 'trace'],
        ],

        // other log targets
    ],
]
```

`ProxyLogTarget` extends [yii\log\Target](https://www.yiiframework.com/doc/api/2.0/yii-log-target) and supports
all settings that yii\log\Target has, but with limitations:
* `exportInterval` must be 1. `yii2-beter-logging` always resets this setting to 1 and notifies about this. See
[details](#further-reading).
* `logVars`, `maskVars` and `prefix` settings will be ignored and will be reset to empty arrays and null values.
See [details](#further-reading).

The only additional setting is `targetLogComponent` section. This is not standard setting for yii2 log Target.
This section is mandatory and it connects `Beter\Yii2BeterLogging\ProxyLogTarget` with
`Beter\Yii2BeterLogging\MonologComponent`.

`Beter\Yii2BeterLogging\MonologComponent` may be configured with few monolog channels, but `ProxyLogTarget`
requires to specify the only one.

> If you need more monolog channels you may setup few `ProxyLogTarget`'s.

Check further doc sections for more details.

### Configure MonologComponent

Log Target passes log entries to `Beter\Yii2BeterLogging\MonologComponent` and then
`Beter\Yii2BeterLogging\MonologComponent` passes them to
[`Monolog\Logger`](https://github.com/Seldaek/monolog/blob/2.x/doc/01-usage.md).

`Beter\Yii2BeterLogging\MonologComponent` configures custom handlers shipped with `yii2-beter-logging`. 

The list of handlers supported:
* `logstash`
* `standard_stream`
* `firephp`

So, configure `Beter\Yii2BeterLogging\MonologComponent` class. Don't forget to use *the same name of
the component and monolog channel name* as was specified in `targetLogComponent` setting of the
`Beter\Yii2BeterLogging\ProxyLogTarget` (`"monolog"` and `"main"` in this example).

```
'monolog' => [
    'class' => Beter\Yii2BeterLogging\MonologComponent::class,
    'channels' => [
        'main' => [
            'handler' => [
                [
                    'name' => 'logstash',
                    'label' => 'logstash',
                    'level' => 'debug',
                    'bubble' => true,
                    'host' => '1.2.3.4', // or host.address.com
                    'port' => 5045,
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
                ],
                [
                    'name' => 'standard_stream',
                    'stream' => 'php://stderr',
                    'level' => 'debug',
                    'bubble' => true,
                    'formatter' => [
                        'name' => 'console',
                        'colorize' => true,
                        'indentSize' => 2,
                        'trace_depth' => 10,
                    ]
                ],
                [
                    'name' => 'firephp',
                    'bubble' => false,
                    'formatter' => [
                        'name' => 'wildfire',
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
],
```

### Configure boostrap order

To not lose any log entries you need to
[boostrap](https://www.yiiframework.com/doc/guide/2.0/en/structure-applications#bootstrap)
`Beter\Yii2BeterLogging\MonologComponent` before any other component. `log` component must be the second component
in the list.

```
// other settings

'bootstrap' => [
    'monolog',
    'log',
    'authManager',
    // other components
],

// other settings
```

### Other configurations

Don't forget to configure all your environments like `cli` and so on.


## Usage

Just use `log` component and `Yii::log()`-related methods as usual

```
Yii::error('Error here', 'application'); // application is a category name
or
Yii::info('Info here', __METHOD__); // if you call from method
```

## Further reading

* [Development and testing](doc/development-and-testing.md)
* [Logging in CLI](doc/logging-in-cli.md)

TBD:
- Handlers, Formatters and Processors
- Delayed errors
- Extending

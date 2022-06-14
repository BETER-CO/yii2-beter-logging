# yii2-beter-logging: Logging with context

## The case

Yii2 doesn't support passing of context to specific log records. Also, it doesn't implement context passing to its
exceptions.

Technically it means that you may call log method as following:

```php
Yii::info('Some message', 'logCategory');

\Yii::$app->log->getLogger()->log('message', \yii\log\Logger::LEVEL_INFO, 'logCategory');
```

The only one default option to add metadata to the log records is to use
[`prefix`](https://www.yiiframework.com/doc/api/2.0/yii-log-target#$prefix-detail) and
[`logVars`](https://www.yiiframework.com/doc/api/2.0/yii-log-target#$logVars-detail).

`prefix` generates only text message and can't be used for more complex structures, it's not the case for logstash too.
`logVars` generates tons to data and not fits for production usage.

The ideal scenario for production grade logging:
* Make it possible to add custom context to every log message and to every exception.
* Make only one record after application initialization and include everything you want
(`$_SERVER`, `$_GET`, `$_POST`, user data, your app settings, everything you want).
* Use `correlationId` to make it possible to distinguish sets of log records between different web requests or CLI
command runs.

But `yii2-beter-logging` makes your life easier and solve described issues.

## How it looks like?

When you finish with this tutorial you may use context as shown below.

```php
$context = [
    'userIp' => Yii::$app->request->userIP,
    'headers' => Yii::$app->request->getHeaders,
];

Yii::info('Incoming request', 'application', $context);

$result = somemethod($arg);
Yii::info('somemethod result', 'application', ['arg' => $arg, 'result' => $result]);
```

You may pass context to exceptions too. To do this you need to install
[beter/exception-with-context](https://packagist.org/packages/beter/exception-with-context) package and:
* use `Beter\ExceptionWithContext\ExceptionWithContext` class;
* or any class that extends `Beter\ExceptionWithContext\ExceptionWithContext`;
* or you may create your own class that implements `Beter\ExceptionWithContext\ExceptionWithContextInterface`;
* or add the trait `Beter\ExceptionWithContext\ExceptionWithContextTrait` to your exception class.

> Check https://github.com/BETER-CO/php-exception-with-context for more details.

```php

function somefunc() {
    $exceptionContext = [
        'userIp' => Yii::$app->request->userIP,
        'headers' => Yii::$app->request->getHeaders,
    ];
    
    $exception = new Beter\ExceptionWithContext\ExceptionWithContext('Something went wrong', 0, null, $exceptionContext);
    // or
    $exception = (new Beter\ExceptionWithContext\ExceptionWithContext('Something went wrong'))->setContext($exceptionContext);
    throw $exception;
}

$startTime = microtime(true);
try {
    somefunc();
} catch (\Throwable $t) {
    $execTime = microtime(true) - $startTime;

    Yii::error($t, __METHOD__, ['execTime' => $execTime]);
    // or Yii::warning, Yii::debug, Yii::info... All of them support exceptions. 
    
}
```

> You may use nested exceptions too!

## Precondition

### `YiiLoggerWithContext`

`yii2-beter-logging` ships with
[`YiiLoggerWithContext` class](https://github.com/BETER-CO/yii2-beter-logging/blob/master/src/YiiLoggerWithContext.php).

This class extends `yii\log\Logger` and redefines
[standard `log` method](https://www.yiiframework.com/doc/api/2.0/yii-log-logger#log()-detail). So,
`YiiLoggerWithContext::log()` allows to add an array as a set of context data. Method doesn't break backward
compatibility, so you may safely change `yii\log\Logger` to `Beter\Yii2BeterLogging\YiiLoggerWithContext`.

### `YiiBase` custom implementation

Also, `YiiBase` defines static methods
[`debug`, `warning`, `info` and `error`](https://www.yiiframework.com/doc/api/2.0/yii-baseyii) and they must be
extended to support context too.

Unfortunately, yii doesn't allow to do it easily. You need to redefine `YiiBase`. It can't be extended, so you need to
copy-paste it.

Use [`Yii.php` file from `yii2-beter-logging` demo app](https://github.com/BETER-CO/yii2-beter-logging/blob/master/deploy/data/php/root/var/www/html/Yii.php).
File is production ready, so don't afraid to use it as is.

This `Yii.php` explicitly uses custom `YiiLoggerWithContext` described earlier.

> You may already have redefined version of `Yii.php`, so, just copy-paste methods and vars related to logging.

After that, you need to change paths in `cli` and `web` bootstrappers:
* [Example for `cli`](https://github.com/BETER-CO/yii2-beter-logging/blob/master/deploy/data/php/root/var/www/html/yii#L15)
* [Example for `web`](https://github.com/BETER-CO/yii2-beter-logging/blob/master/deploy/data/php/root/var/www/html/web/index.php#L8)

## Exceptions with context

You have 2 options.
1. You may use [`Beter\ExceptionWithContext\ExceptionWithContext` class](https://github.com/BETER-CO/php-exception-with-context/blob/master/src/ExceptionWithContext.php)
class as a base or custom exception in your app.
[Check an example for the yii app](https://github.com/BETER-CO/yii2-beter-logging/blob/master/deploy/data/php/root/var/www/html/exception/ExceptionWithContext.php).
2. You may add [`Beter\ExceptionWithContext\ExceptionWithContextTrait` trait](https://github.com/BETER-CO/php-exception-with-context/blob/master/src/ExceptionWithContextTrait.php)
to your current exception implementations.
[Check an example for the yii app](https://github.com/BETER-CO/yii2-beter-logging/blob/master/deploy/data/php/root/var/www/html/exception/ExceptionWithTrait.php).
3. You may create your own class that extends `\Exception`
class and implements [`Beter\ExceptionWithContext\ExceptionWithContextInterface` interface](https://github.com/BETER-CO/php-exception-with-context/blob/master/src/ExceptionWithContextInterface.php)

`yii2-beter-logging` support nested exceptions too. The first exception in the chain may not be an object that
implements [`Beter\Yii2BeterLogging\ExceptionWithContextInterface`](https://github.com/BETER-CO/yii2-beter-logging/blob/master/src/ExceptionWithContextInterface.php).

Few [`ProxyLogTarget`](https://github.com/BETER-CO/yii2-beter-logging/blob/master/src/ProxyLogTarget.php) settings
control `depth` of check for nested exceptions and max amount of context entries that must be collected:
* `exceptionContextEntriesLimit` - max amount of context entries to log
* `exceptionContextMaxDepthToAnalyze` - limits the depth of analysis to prevent infinite loops or reduce resources consumption.

You may
[check examples](https://github.com/BETER-CO/yii2-beter-logging/blob/master/deploy/data/php/root/var/www/html/commands/BeterLoggingController.php#L222)
and even [run them](development-and-testing.md).

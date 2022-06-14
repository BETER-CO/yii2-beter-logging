<?php

use yii\BaseYii;
use yii\log\Logger;

/**
 * Yii is a helper class serving common framework functionalities.
 *
 * It extends from [[\yii\BaseYii]] which provides the actual implementation.
 * This class changes:
 *  - static function debug and allows logging of debug level even with YII_DEBUG === false
 */
class Yii extends BaseYii
{
    private static $_logger;

    /**
     * Logs a debug message.
     * Trace messages are logged mainly for development purpose to see
     * the execution work flow of some code.
     *
     * This method changes default behaviour and logs messages even with debug mode off false.
     * Only yii\log\Logger, yii\log\Dispatcher and LogTargets may decide what level to log.
     *
     * @param string|array|\Throwable $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array or even \Throwable.
     * @param string $category the category of the message.
     * @param array $context meta information related to the log record.
     */
    public static function debug($message, $category = 'application', array $context = [])
    {
        static::getLogger()->log($message, Logger::LEVEL_TRACE, $category, $context);
    }

    /**
     * Alias of [[debug()]].
     *
     * @param string|array|\Throwable $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as an array or even \Throwable.
     * @param string $category the category of the message.
     * @param array $context meta information related to the log record.
     *
     * @deprecated since 2.0.14. Use [[debug()]] instead.
     */
    public static function trace($message, $category = 'application', array $context = [])
    {
        static::debug($message, $category, $context);
    }

    /**
     * Logs an error message.
     *
     * An error message is typically logged when an error occurs during the execution of an application.
     *
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as an array.
     * @param string $category the category of the message.
     * @param array $context meta information related to the log record.
     */
    public static function error($message, $category = 'application', array $context = [])
    {
        static::getLogger()->log($message, Logger::LEVEL_ERROR, $category, $context);
    }

    /**
     * Logs a warning message.
     *
     * A warning message is typically logged when an error occurs while the execution can still continue.
     *
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as an array.
     * @param string $category the category of the message.
     * @param array $context meta information related to the log record.
     */
    public static function warning($message, $category = 'application', array $context = [])
    {
        static::getLogger()->log($message, Logger::LEVEL_WARNING, $category, $context);
    }

    /**
     * Logs an informative message.
     * An informative message is typically logged by an application to keep record of
     * something important (e.g. an administrator logs in).
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as an array.
     * @param string $category the category of the message.
     * @param array $context meta information related to the log record.
     */
    public static function info($message, $category = 'application', array $context = [])
    {
        static::getLogger()->log($message, Logger::LEVEL_INFO, $category, $context);
    }

    /**
     * @return Logger message logger
     */
    public static function getLogger()
    {
        if (self::$_logger !== null) {
            return self::$_logger;
        }

        return self::$_logger = static::createObject(Beter\Yii2\Logging\YiiLoggerWithContext::class);
    }

    /**
     * Sets the logger object.
     * @param Logger $logger the logger object.
     */
    public static function setLogger($logger)
    {
        self::$_logger = $logger;
    }
}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = require __DIR__ . '/vendor/yiisoft/yii2/classes.php';
Yii::$container = new yii\di\Container();

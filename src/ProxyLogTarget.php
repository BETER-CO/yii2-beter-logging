<?php

namespace Beter\Yii2BeterLogging;

use Yii;
use yii\log\Target;
use yii\log\Logger as YiiLogger;
use Monolog\Logger as MonologLogger;
use Beter\Yii2BeterLogging\Exception\InvalidConfigException;
use Beter\Yii2BeterLogging\Exception\UnknownLogLevelException;


class ProxyLogTarget extends Target
{

    /**
     * Parent class sets default value, but this Log Target doesn't support that. Need to unset manually.
     *
     * @var array
     */
    public $logVars = [];

    /**
     * Parent class sets default value, but this Log Target doesn't support that. Need to unset manually.
     *
     * @var array
     */
    public $maskVars = [];

    /**
     * Any log entries must be passed immediately to Monolog object. Buffering must be implemented using
     * Monolog's BufferHandler.
     *
     * @var array
     */
    public $exportInterval = 1;

    protected static $_yiiToPsr3LogLevelMapping = [
        YiiLogger::LEVEL_ERROR => MonologLogger::ERROR,
        YiiLogger::LEVEL_WARNING => MonologLogger::WARNING,
        YiiLogger::LEVEL_INFO => MonologLogger::INFO,
        YiiLogger::LEVEL_TRACE => MonologLogger::DEBUG,
        YiiLogger::LEVEL_PROFILE_BEGIN => MonologLogger::DEBUG,
        YiiLogger::LEVEL_PROFILE_END => MonologLogger::DEBUG,
        YiiLogger::LEVEL_PROFILE => MonologLogger::DEBUG,
    ];

    protected array $_delayedMessages = [];

    protected ?MonologLogger $_logger = null;

    /**
     * Method mimics to yii\log\Logger::log behaviour of storing log entries.
     * You must use this method only during Yii component init phase. After initialization
     * just use Yii::info(), Yii:error() and so on.
     *
     * @param \Throwable $throwable
     * @param $level
     * @param $logCategory
     * @return void
     */
    protected function addDelayedError(\Throwable $throwable, $level, $logCategory) {
        $this->_delayedMessages[] = [
            $throwable,
            $level,
            $logCategory,
            microtime(true),
            [],
            memory_get_usage()
        ];
    }

    protected function addDelayedMessage(array $message) {
        $this->_delayedMessages[] = $message;
    }

    /**
     * @throws InvalidConfigException
     */
    protected function reportErrorDuringInit($method, $errorText): bool
    {
        // We can't throw exceptions in the middle of the component init process
        $errorText = '[' . $method . '] ' . $errorText;
        $result = error_log($errorText);

        if (YII_DEBUG) {
            throw new InvalidConfigException($errorText);
        }

        return $result;
    }

    /**
     * @param $settings
     *
     * @return bool|void
     *
     * @throws \yii\base\InvalidConfigException
     * @throws InvalidConfigException
     */
    public function setTargetLogComponent($settings) {
        foreach (['componentName', 'logChannel'] as $setting) {
            if (!isset($settings[$setting])) {
                return $this->reportErrorDuringInit(__METHOD__, "$setting must be set");
            }
        }

        if (!Yii::$app->has($settings['componentName'], true)) {
            return $this->reportErrorDuringInit(
                __METHOD__,
                'Target log component must be initialized before the yii log component'
            );
        }

        $targetLogComponent = Yii::$app->get($settings['componentName']);

        foreach (['getLogger', 'hasLogger'] as $methodToCheck) {
            if (!$targetLogComponent->hasMethod($methodToCheck)) {
                return $this->reportErrorDuringInit(
                    __METHOD__,
                    "Target log component must have $methodToCheck method"
                );
            }
        }

        $logChannel = $settings['logChannel'];
        if (!$targetLogComponent->hasLogger($logChannel)) {
            return $this->reportErrorDuringInit(__METHOD__, "logChannel '$logChannel' doesn't exist");
        }

        $logger = $targetLogComponent->getLogger($logChannel);

        if (!$logger instanceof MonologLogger) {
            return $this->reportErrorDuringInit(__METHOD__, 'Logger must be an object of ' .  MonologLogger::class);
        }

        $this->_logger = $logger;
    }

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if ($this->_logger === null) {
            return $this->reportErrorDuringInit(
                __METHOD__,
                'Logging through ' . __CLASS__ . ' will be stopped! targetLogComponent property was not defined ' .
                    '(or correctly defined) in configuration for the ' . __CLASS__
            );
        }

        if ($this->exportInterval !== 1) {
            $this->addDelayedError(
                new InvalidConfigException(__CLASS__ . ' must not control exportInterval settings, ' .
                    'you must use Monolog BufferHandler instead. exportInterval was set back to 1'),
                YiiLogger::LEVEL_WARNING,
                __METHOD__
            );

            $this->exportInterval = 1;
        }

        if (isset($this->logVars) && !empty($this->logVars)) {
            $this->addDelayedError(
                new InvalidConfigException('logVars setting has no effect on the '. __CLASS__ . ' and ' .
                    'Monolog logging flow, use Monolog processors instead'),
                YiiLogger::LEVEL_INFO,
                __METHOD__
            );
        }

        if (isset($this->maskVars) && !empty($this->maskVars)) {
            $this->addDelayedError(
                new InvalidConfigException('maskVars setting has no effect on the '. __CLASS__ . ' and ' .
                    'Monolog logging flow, use Monolog processors or formatters instead'),
                YiiLogger::LEVEL_INFO,
                __METHOD__
            );
        }

        if (isset($this->prefix)) {
            $this->addDelayedError(
                new InvalidConfigException('prefix setting has no effect on the ' . __CLASS__ . ' and ' .
                    'Monolog logging flow, use Monolog processors or formatters instead'),
                YiiLogger::LEVEL_INFO,
                __METHOD__
            );
        }
    }

    /**
     * Processes the given log messages.
     * Almost the same as default Yii Target class method does, but adds feature to deliver delayed messages.
     * Method tracks repeating calls of collect of the same object in cycle  and creates delayed queue while in
     * cycle.
     *
     * @param array $messages log messages to be processed. See [[Logger::messages]] for the structure
     * of each message.
     * @param bool $final whether this method is called at the end of the current application
     */
    public function collect($messages, $final)
    {
        $newFilteredMessages = static::filterMessages($messages, $this->getLevels(), $this->categories, $this->except);

        if ($this->exportInterval === 0) {
            // collect is triggered while other collect call is in progress
            foreach ($newFilteredMessages as $message) {
                $this->addDelayedMessage($message);
            }

            return;
        }

        if (!empty($this->_delayedMessages)) {
            $this->messages = array_merge($this->messages, $this->_delayedMessages);
            $this->_delayedMessages = [];
        }

        $this->messages = array_merge($this->messages, $newFilteredMessages);
        $count = count($this->messages);
        if ($count > 0 && ($final || $this->exportInterval > 0 && $count >= $this->exportInterval)) {
            // set exportInterval to 0 to avoid triggering export again while exporting
            $oldExportInterval = $this->exportInterval;
            $this->exportInterval = 0;
            $this->export();
            $this->exportInterval = $oldExportInterval;

            $this->messages = [];
        }
    }

    /**
     * @{inheritdoc}
     */
    public function export() {
        if ($this->_logger === null) {
            return;
        }

        foreach ($this->messages as $messageArray) {
            $context = [];
            $level = $this->convertToPsr3Level($messageArray[1]);
            $message = $messageArray[0];

            if ($message instanceof \Throwable) {
                $context['exception'] = $message;
                $message = $message->getMessage();
            }

            $context['log.category'] = $messageArray[2];
            $context['log.trueTime'] = \DateTime::createFromFormat(
                'U.u', $messageArray[3], $this->_logger->getTimezone()
            );
            $context['mem.usage'] = $messageArray[5];
            if (!empty($messageArray[4])) {
                $context['log.trace'] = $messageArray[4];
            }

            $this->_logger->log($level, $message, $context);
        }
    }

    public function convertToPsr3Level($yiiLogLevel) {
        if (!isset(static::$_yiiToPsr3LogLevelMapping[$yiiLogLevel])) {
            $this->addDelayedError(
                new UnknownLogLevelException("Unknown yii log level ${yiiLogLevel}, replaced with INFO level"),
                YiiLogger::LEVEL_WARNING,
                __METHOD__
            );

            return MonologLogger::INFO;
        }

        return static::$_yiiToPsr3LogLevelMapping[$yiiLogLevel];
    }

    /**
     * Redefines method in parent class to block any context mixins
     *
     * @return string
     */
    public function getContextMessage() {
        return '';
    }

    /**
     * Redefines method in parent class to block any context mixins
     *
     * @return string
     */
    public function getMessagePrefix($message) {
        return '';
    }
}

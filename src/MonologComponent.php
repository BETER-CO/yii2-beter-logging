<?php

namespace Beter\Yii2BeterLogging;

use Beter\Yii2BeterLogging\Handler\HandlerWithHandleErrorProcessingInterface;

use Yii;
use Monolog\Logger;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Formatter\WildfireFormatter;
use Monolog\Formatter\FormatterInterface;
use Monolog\Processor\ProcessorInterface;
use Beter\Yii2BeterLogging\Handler\LogstashHandler;
use Beter\Yii2BeterLogging\Handler\HandlerWithStatsInterface;
use Beter\Yii2BeterLogging\Processor\BasicProcessor;
use Beter\Yii2BeterLogging\Formatter\LogstashFormatter;
use Beter\Yii2BeterLogging\Formatter\ConsoleFormatter;
use Beter\Yii2BeterLogging\Exception\LoggerNotFoundException;
use Beter\Yii2BeterLogging\Exception\InvalidConfigException;
use yii\base\Component;

class MonologComponent extends Component
{
    const DEFAULT_TRACE_DEPTH = 10;

    /**
     * @var array Channels
     */
    public $channels;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (!isset($this->channels['main'])) {
            $this->processInitException(new InvalidConfigException('Channel "main" must be defined'));
        }

        foreach ($this->channels as $name => &$channel) {
            $handlers = [];
            $processors = [];
            if (!empty($channel['handler']) && is_array($channel['handler'])) {
                foreach ($channel['handler'] as &$handlerConfig) {
                    try {
                        if (!is_array($handlerConfig)) {
                            throw new InvalidConfigException("Incorrect handler config. Channel: $name");
                        }

                        $handler = $this->createHandlerInstance($handlerConfig);
                        $handlers[] = $handler;
                    } catch (\Throwable $throwable) {
                        $e = new InvalidConfigException("Creation of handler '$name' has failed", 0, $throwable);
                        $this->processInitException($e);
                    }
                }
            }

            if (!empty($channel['processor']) && is_array($channel['processor'])) {
                foreach ($channel['processor'] as $processorConfig) {
                    try {
                        $processors[] = $this->createProcessorInstance($processorConfig);
                    } catch (\Throwable $throwable) {
                        $e = new InvalidConfigException("Creation of processor has failed", 0, $throwable);
                        $this->processInitException($e);
                    }
                }
            }

            $channel = new Logger($name, $handlers, $processors);
            $channel->setExceptionHandler([$this, 'processMonologAddRecordError']);
        }

        $this->disableHandlersWithNonUniqueLabels();
    }

    protected function disableHandlersWithNonUniqueLabels()
    {
        $labelNames = [];
        $channelNames = array_keys($this->channels);

        foreach ($channelNames as $channelName) {
            if (!$this->hasLogger($channelName)) {
                continue;
            }

            $handlers = $this->getLogger($channelName)->getHandlers();
            foreach ($handlers as $handler) {
                if ($handler instanceof HandlerWithStatsInterface) {
                    /* @var $handler HandlerWithStatsInterface */
                    $label = $handler->getLabel();
                    if (isset($labelNames[$label])) {
                        $handler->disableStats();
                        $this->processInitException(
                            new InvalidConfigException(
                                "Channel '$channelName' contains handler with label value '$label' " .
                                'that was already used in other handlers. Stats for this handler was disabled.'
                            )
                        );
                    } else {
                        $labelNames[$label] = true;
                    }
                }
            }
        }
    }

    /**
     * @param $handler
     * @param \Throwable $throwable
     * @param array|null $record
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function processHandlerErrorDuringHandleMethodCall($handler, \Throwable $throwable, ?array $record = null) {
        Yii::error($throwable, __METHOD__);
    }

    /**
     * @param \Throwable $throwable
     * @param array|null $record
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function processMonologAddRecordError(\Throwable $throwable, ?array $record = null) {
        Yii::error($throwable, __METHOD__);
    }

    /**
     * Creates Monolog Handler and sets Monolog Formatter
     *
     * @param array $config config passed from yii config to initialize MonologComponent
     *
     * @throws InvalidConfigException
     * @throws \Throwable subset of Monolog exceptions
     */
    protected function createHandlerInstance(array $config): HandlerInterface
    {
        $handler = $this->handlerFactory($config);

        if ($handler instanceof HandlerWithStatsInterface) {
            if (!isset($config['label'])) {
                $label = $config['name'] ?? '';
            } else {
                $label = $config['label'];
            }

            $handler->setLabel($label);
        }

        if ($handler instanceof HandlerWithHandleErrorProcessingInterface) {
            $handler->setHandleExceptionHandler([$this, 'processHandlerErrorDuringHandleMethodCall']);

            $maxHandleErrors = $config['max_handle_errors_before_disabling'] ?? 2;
            $handler->setMaxHandleErrorsBeforeDisabling($maxHandleErrors);
        }

        if (isset($config['formatter'])) {
            $formatter = $this->formatterFactory($config['formatter']);
            $handler->setFormatter($formatter);
        }

        return $handler;
    }

    /**
     * Creates Monolog Processor. Only supports 'basic_processor' right now.
     *
     * @param array $config passed from yii config to initialize MonologComponent
     *
     * @throws InvalidConfigException
     */
    protected function createProcessorInstance(array $config): ProcessorInterface
    {
        if (!isset($config['name'])) {
            throw new InvalidConfigException("Processor must have 'name' setting");
        }

        $name = $config['name'];
        switch ($name) {
            case 'basic_processor':
                foreach (['env', 'app', 'service', 'host'] as $settingName) {
                    if (!isset($config[$settingName])) {
                        throw new InvalidConfigException("Processor '$name' must have '$settingName' setting");
                    }
                }

                return new BasicProcessor($config['env'], $config['app'], $config['service'], $config['host']);
            default:
                throw new InvalidConfigException("Unsupported processor name $name");
        }
    }

    /**
     * Creates Monolog Formatter
     *
     * @param array $config config passed from yii config to initialize MonologComponent
     *
     * @throws InvalidConfigException
     * @throws \Throwable subset of Monolog exceptions
     */
    protected function formatterFactory(array $config): FormatterInterface
    {
        if (!isset($config['name'])) {
            throw new InvalidConfigException("Formatter array must have 'name' setting");
        }

        $traceDepth = $config['trace_depth'] ?? static::DEFAULT_TRACE_DEPTH;

        $name = $config['name'];
        switch ($name) {
            case 'logstash':
                return new LogstashFormatter($traceDepth);
            case 'console':
                $colorize = $config['colorize'] ?? false;
                $indentSize = $config['indentSize'] ?? 0;
                return new ConsoleFormatter($colorize, $indentSize, $traceDepth);
            case 'wildfire':
                return new WildfireFormatter();
            default:
                throw new InvalidConfigException("Unsupported formatter name '$name'");
        }
    }

    /**
     * Creates handler instance.
     *
     * @param array $config Configuration parameters
     *
     * @return HandlerInterface
     *
     * @throws InvalidConfigException
     * @throws \Throwable subset of Monolog exceptions
     */
    protected function handlerFactory(array $config): HandlerInterface
    {
        if (!isset($config['name'])) {
            throw new InvalidConfigException('Handler must have name setting');
        }

        if (!isset($config['level'])) {
            throw new InvalidConfigException('Level must be set');
        }

        $config = array_merge(['bubble' => true], $config);

        $config['level'] = Logger::toMonologLevel($config['level']);

        $name = $config['name'];
        switch ($name) {
            case 'logstash':
                $mandatoryLogstashSettingNames = [
                    'host', 'port', 'socket_transport', 'persistent', 'socket_timeout', 'writing_timeout',
                    'connection_timeout'
                ];

                foreach ($mandatoryLogstashSettingNames as $settingName) {
                    if (!isset($config[$settingName])) {
                        throw new InvalidConfigException("Handler '$name' must have '$settingName' setting");
                    }
                }

                return new LogstashHandler(
                    $config['host'],
                    $config['port'],
                    $config['socket_transport'],
                    $config['level'],
                    $config['bubble'],
                    $config['persistent'],
                    $config['socket_timeout'],
                    $config['writing_timeout'],
                    $config['connection_timeout'],
                    isset($config['chunk_size']) ?? null
                );
            case 'standard_stream':
                if (!isset($config['stream'])) {
                    throw new InvalidConfigException("Handler '$name' must have 'stream' setting");
                }

                if ($config['stream'] !== 'php://stdout' && $config['stream'] !== 'php://stderr') {
                    throw new InvalidConfigException(
                        "Config name '$name' setting 'stream' has incorrect value"
                    );
                }

                return new StreamHandler($config['stream'], $config['level'], $config['bubble']);
            case 'firephp':
                return new FirePHPHandler($config['level'], $config['bubble']);
            default:
                throw new InvalidConfigException("Unsupported handler name '$name'");
        }
    }

    public function getStats() {
        $stats = [];

        $channelNames = array_keys($this->channels);
        foreach ($channelNames as $channelName) {
            if (!$this->hasLogger($channelName)) {
                continue;
            }

            $handlers = $this->getLogger($channelName)->getHandlers();
            foreach ($handlers as $handler) {
                if ($handler instanceof HandlerWithStatsInterface) {
                    if (!isset($stats[$channelName])) {
                        $stats[$channelName] = [];
                    }

                    /* @var $handler HandlerWithStatsInterface */
                    $stats[$channelName][$handler->getLabel()] = $handler->getStats();
                }
            }
        }

        return $stats;
    }

    /**
     * Return logger object.
     *
     * @param string $name Logger name
     *
     * @return Logger Logger object
     *
     * @throws LoggerNotFoundException
     */
    public function getLogger(string $name = 'main'): Logger
    {
        if (!$this->hasLogger($name)) {
            throw new LoggerNotFoundException(sprintf("Logger instance '%s' not found", $name));
        }

        return $this->channels[$name];
    }

    /**
     * Checks if the given logger exists.
     *
     * @param string $name Logger name
     *
     * @return bool
     */
    public function hasLogger(string $name): bool
    {
        return isset($this->channels[$name]) && ($this->channels[$name] instanceof Logger);
    }

    /**
     * Tries to deliver error using different channels. It may be an issue because at time of initialization
     * of MonologComponent the Yii2 Log component may be not initialized yet or may fail.
     *
     * @param \Throwable $throwable throwable to handle
     * @return void
     * @throws \Throwable only if YII_DEBUG is set
     */
    protected function processInitException(\Throwable $throwable): void
    {
        error_log($throwable->getMessage());
        Yii::error($throwable);

        if (YII_DEBUG) {
            throw $throwable;
        }
    }
}

<?php

namespace app\helpers;

use Yii;
use Beter\Yii2BeterLogging\ProxyLogTarget;
use Beter\Yii2BeterLogging\MonologComponent;

class BeterLoggingInitializer
{
    const TARGET_LOG_COMPONENT = 'monolog';
    const TARGET_LOG_CHANNEL = 'main';

    static function initLog($newLogComponentDefinition)
    {
        $debugLogTarget = self::getDebugLogTarget();

        \Yii::$app->set('log', $newLogComponentDefinition);

        // Get of the component forces Yii to lazyload and init component.
        \Yii::$app->get('log');

        if ($debugLogTarget !== null) {
            \Yii::$app->log->targets['debug'] = $debugLogTarget;
        }
    }

    static function initTargetLog($targetLogComponentDefinition)
    {
        \Yii::$app->set(self::TARGET_LOG_COMPONENT, $targetLogComponentDefinition);

        // Get of the component forces Yii to lazyload and init component.
        \Yii::$app->get(self::TARGET_LOG_COMPONENT);
    }

    static function getDebugLogTarget()
    {
        if (isset(Yii::$app->log->targets['debug'])) {
            return Yii::$app->log->targets['debug'];
        }

        return null;
    }

    static function getLogComponentDefinition()
    {
        $componentDefinitions = Yii::$app->getComponents(true);
        return $componentDefinitions['log'];
    }

    static function createLogComponentDefinition($traceLevel, $categories, $except, $levels)
    {
        $class = BeterLoggingInitializer::getLogComponentDefinition()['class'];
        $definition = [
            'class' => $class,
            'traceLevel' => $traceLevel,
            'flushInterval' => 1,
            'targets' => [
                'monolog-proxy' => [
                    'class' => ProxyLogTarget::class,
                    'targetLogComponent' => [
                        'componentName' => self::TARGET_LOG_COMPONENT,
                        'logChannel' => self::TARGET_LOG_CHANNEL
                    ],
                    'exportInterval' => 1,
                    'categories' => $categories,
                    'except' => $except,
                    'levels' => $levels,
                ],
            ],
        ];

        return $definition;
    }

    static function createLogstashHandler($level = 'debug', $bubble = true, $host = '127.0.0.1', $port = 5555)
    {
        $handlerConfig = [
            'name' => 'logstash',
            'label' => 'logstash',
            'level' => $level,
            'bubble' => $bubble,
            'host' => $host, // or host.address.com
            'port' => $port,
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

        return $handlerConfig;
    }

    static function createStandardStreamHandler($level = 'debug', $bubble = true, $colorize = true, $indentSize = 2)
    {
        $handlerConfig = [
            'name' => 'standard_stream',
            'stream' => 'php://stderr',
            'level' => $level,
            'bubble' => $bubble,
            'formatter' => [
                'name' => 'console',
                'colorize' => $colorize,
                'indentSize' => $indentSize,
                'trace_depth' => 10,
            ]
        ];

        return $handlerConfig;
    }

    static function createProductionStandardStreamHandler($level = 'debug', $bubble = true)
    {
        $handlerConfig = [
            'name' => 'standard_stream',
            'stream' => 'php://stderr',
            'level' => $level,
            'bubble' => $bubble,
            'formatter' => [
                'name' => 'logstash',
                'trace_depth' => 10,
            ]
        ];

        return $handlerConfig;
    }

    static function createBasicProcessor()
    {
        $processorConfig = [
            'name' => 'basic_processor',
            'env' => YII_ENV, // dev, prod, etc
            'app' => 'yii-test-app',
            'service' => 'web',
            'host' => gethostname(), // or set it as you want
        ];

        return $processorConfig;
    }

    static function createCorrelationIdProcessor()
    {
        $processorConfig = [
            'name' => 'correlation_id_processor',
            'length' => 32,
            'search_in_headers' => true,
            'header_name' => 'X-Request-Id',
        ];

        return $processorConfig;
    }

    static function createMonologComponentDefinition($handlers = [], $processors = [])
    {
        $definition = [
            'class' => MonologComponent::class,
            'channels' => [
                self::TARGET_LOG_CHANNEL => [
                    'handler' => $handlers,
                    'processor' => $processors,
                ],
            ],
        ];

        return $definition;
    }
}

<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\log\Logger;
use app\exception\ExceptionWithContext;
use app\helpers\BeterLoggingInitializer;


class BeterLoggingController extends Controller
{

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionDefaultLogTargets()
    {
        $logComponentDefinition = BeterLoggingInitializer::getLogComponentDefinition();

        return $this->render(
            'log_target',
            [
                'actionName' => __METHOD__,
                'data' => [
                    'YII_DEBUG' => YII_DEBUG,
                    'Initial log component definition' => $logComponentDefinition,
                ]
            ]
        );
    }

    public function actionLogTargetAndHandlerLevel()
    {
        $logComponentDefinition = BeterLoggingInitializer::getLogComponentDefinition();

        $standardStreamHandler = BeterLoggingInitializer::createStandardStreamHandler('debug');
        $handlers = [$standardStreamHandler];

        $basicProcessor = BeterLoggingInitializer::createBasicProcessor();
        $processors = [$basicProcessor];

        $targetLogComponentDefinition = BeterLoggingInitializer::createMonologComponentDefinition($handlers, $processors);
        BeterLoggingInitializer::initTargetLog($targetLogComponentDefinition);

        $traceLevel = 3;
        $categories = [];
        $except = [];
        $levels = ['error', 'warning'];
        $newLogComponentDefinition = BeterLoggingInitializer::createLogComponentDefinition(
            $traceLevel, $categories, $except, $levels
        );

        BeterLoggingInitializer::initLog($newLogComponentDefinition);

        \Yii::info('Info message', 'application');

        return $this->render(
            'log_target',
            [
                'actionName' => __METHOD__,
                'data' => [
                    'YII_DEBUG' => YII_DEBUG,
                    'Target log component definition' => $targetLogComponentDefinition,
                    'Initial log component definition' => $logComponentDefinition,
                    'Reinitialized log component definition' => $newLogComponentDefinition,
                ]
            ]
        );
    }

    public function actionColorizedStandardStream()
    {
        $logComponentDefinition = BeterLoggingInitializer::getLogComponentDefinition();

        $standardStreamHandler = BeterLoggingInitializer::createStandardStreamHandler('debug', true, true, 4);
        $handlers = [$standardStreamHandler];

        $basicProcessor = BeterLoggingInitializer::createBasicProcessor();
        $processors = [$basicProcessor];

        $targetLogComponentDefinition = BeterLoggingInitializer::createMonologComponentDefinition($handlers, $processors);
        BeterLoggingInitializer::initTargetLog($targetLogComponentDefinition);

        $traceLevel = 3;
        $categories = ['application'];
        $except = [];
        $levels = ['error', 'warning', 'info', 'trace'];
        $newLogComponentDefinition = BeterLoggingInitializer::createLogComponentDefinition(
            $traceLevel, $categories, $except, $levels
        );

        BeterLoggingInitializer::initLog($newLogComponentDefinition);

        \Yii::debug('Debug message', 'application');
        \Yii::info('Info message', 'application');
        \Yii::warning('Warning message', 'application');
        \Yii::error('Error message', 'application');

        \Yii::debug(new \Exception('Debug exception'), 'application');
        \Yii::info(new \Exception('Info exception'), 'application');
        \Yii::warning(new \Exception('Info exception'), 'application');
        \Yii::error(new \Exception('Info exception'), 'application');

        return $this->render(
            'log_target',
            [
                'actionName' => __METHOD__,
                'data' => [
                    'YII_DEBUG' => YII_DEBUG,
                    'Target log component definition' => $targetLogComponentDefinition,
                    'Initial log component definition' => $logComponentDefinition,
                    'Reinitialized log component definition' => $newLogComponentDefinition,
                ]
            ]
        );
    }

    public function actionNoPrettyPrintingStandardStream()
    {
        $logComponentDefinition = BeterLoggingInitializer::getLogComponentDefinition();

        $standardStreamHandler = BeterLoggingInitializer::createStandardStreamHandler('debug', true, false, 0);
        $handlers = [$standardStreamHandler];

        $basicProcessor = BeterLoggingInitializer::createBasicProcessor();
        $processors = [$basicProcessor];

        $targetLogComponentDefinition = BeterLoggingInitializer::createMonologComponentDefinition($handlers, $processors);
        BeterLoggingInitializer::initTargetLog($targetLogComponentDefinition);

        $traceLevel = 3;
        $categories = [];
        $except = [];
        $levels = ['error', 'warning', 'info', 'trace'];
        $newLogComponentDefinition = BeterLoggingInitializer::createLogComponentDefinition(
            $traceLevel, $categories, $except, $levels
        );

        BeterLoggingInitializer::initLog($newLogComponentDefinition);

        \Yii::debug('Debug message', 'application');
        \Yii::info('Info message', 'application');
        \Yii::warning('Warning message', 'application');
        \Yii::error('Error message', 'application');

        \Yii::debug(new \Exception('Debug exception'), 'application');
        \Yii::info(new \Exception('Info exception'), 'application');
        \Yii::warning(new \Exception('Info exception'), 'application');
        \Yii::error(new \Exception('Info exception'), 'application');

        return $this->render(
            'log_target',
            [
                'actionName' => __METHOD__,
                'data' => [
                    'YII_DEBUG' => YII_DEBUG,
                    'Target log component definition' => $targetLogComponentDefinition,
                    'Initial log component definition' => $logComponentDefinition,
                    'Reinitialized log component definition' => $newLogComponentDefinition,
                ]
            ]
        );
    }

    public function actionProductionStandardStream()
    {
        $logComponentDefinition = BeterLoggingInitializer::getLogComponentDefinition();

        $standardStreamHandler = BeterLoggingInitializer::createProductionStandardStreamHandler('debug', true);
        $handlers = [$standardStreamHandler];

        $basicProcessor = BeterLoggingInitializer::createBasicProcessor();
        $processors = [$basicProcessor];

        $targetLogComponentDefinition = BeterLoggingInitializer::createMonologComponentDefinition($handlers, $processors);
        BeterLoggingInitializer::initTargetLog($targetLogComponentDefinition);

        $traceLevel = 3;
        $categories = [];
        $except = [];
        $levels = ['error', 'warning', 'info', 'trace'];
        $newLogComponentDefinition = BeterLoggingInitializer::createLogComponentDefinition(
            $traceLevel, $categories, $except, $levels
        );

        BeterLoggingInitializer::initLog($newLogComponentDefinition);

        \Yii::debug('Debug message', 'application');
        \Yii::info('Info message', 'application');
        \Yii::warning('Warning message', 'application');
        \Yii::error('Error message', 'application');

        \Yii::debug(new \Exception('Debug exception'), 'application');
        \Yii::info(new \Exception('Info exception'), 'application');
        \Yii::warning(new \Exception('Info exception'), 'application');
        \Yii::error(new \Exception('Info exception'), 'application');

        return $this->render(
            'log_target',
            [
                'actionName' => __METHOD__,
                'data' => [
                    'YII_DEBUG' => YII_DEBUG,
                    'Target log component definition' => $targetLogComponentDefinition,
                    'Initial log component definition' => $logComponentDefinition,
                    'Reinitialized log component definition' => $newLogComponentDefinition,
                ]
            ]
        );
    }

    public function actionBubbling1()
    {
        $logComponentDefinition = BeterLoggingInitializer::getLogComponentDefinition();

        $standardStreamHandler1 = BeterLoggingInitializer::createStandardStreamHandler('info', false);
        $standardStreamHandler2 = BeterLoggingInitializer::createStandardStreamHandler('info', false);
        $handlers = [$standardStreamHandler1, $standardStreamHandler2];

        $basicProcessor = BeterLoggingInitializer::createBasicProcessor();
        $processors = [$basicProcessor];

        $targetLogComponentDefinition = BeterLoggingInitializer::createMonologComponentDefinition($handlers, $processors);
        BeterLoggingInitializer::initTargetLog($targetLogComponentDefinition);

        $traceLevel = 3;
        $categories = ['application'];
        $except = [];
        $levels = ['error', 'warning', 'info'];
        $newLogComponentDefinition = BeterLoggingInitializer::createLogComponentDefinition(
            $traceLevel, $categories, $except, $levels
        );

        BeterLoggingInitializer::initLog($newLogComponentDefinition);

        \Yii::info('Info message', 'application');

        return $this->render(
            'log_target',
            [
                'actionName' => __METHOD__,
                'data' => [
                    'YII_DEBUG' => YII_DEBUG,
                    'Target log component definition' => $targetLogComponentDefinition,
                    'Initial log component definition' => $logComponentDefinition,
                    'Reinitialized log component definition' => $newLogComponentDefinition,
                ]
            ]
        );
    }

    public function actionBubbling2()
    {
        $logComponentDefinition = BeterLoggingInitializer::getLogComponentDefinition();

        $standardStreamHandler1 = BeterLoggingInitializer::createStandardStreamHandler('info', true);
        $standardStreamHandler2 = BeterLoggingInitializer::createStandardStreamHandler('info', true);
        $handlers = [$standardStreamHandler1, $standardStreamHandler2];

        $basicProcessor = BeterLoggingInitializer::createBasicProcessor();
        $processors = [$basicProcessor];

        $targetLogComponentDefinition = BeterLoggingInitializer::createMonologComponentDefinition($handlers, $processors);
        BeterLoggingInitializer::initTargetLog($targetLogComponentDefinition);

        $traceLevel = 3;
        $categories = ['application'];
        $except = [];
        $levels = ['error', 'warning', 'info'];
        $newLogComponentDefinition = BeterLoggingInitializer::createLogComponentDefinition(
            $traceLevel, $categories, $except, $levels
        );

        BeterLoggingInitializer::initLog($newLogComponentDefinition);

        \Yii::info('Info message', 'application');

        return $this->render(
            'log_target',
            [
                'actionName' => __METHOD__,
                'data' => [
                    'YII_DEBUG' => YII_DEBUG,
                    'Target log component definition' => $targetLogComponentDefinition,
                    'Initial log component definition' => $logComponentDefinition,
                    'Reinitialized log component definition' => $newLogComponentDefinition,
                ]
            ]
        );
    }

    public function actionBubbling3()
    {
        $logComponentDefinition = BeterLoggingInitializer::getLogComponentDefinition();

        $standardStreamHandler1 = BeterLoggingInitializer::createStandardStreamHandler('warning', true);
        $standardStreamHandler2 = BeterLoggingInitializer::createStandardStreamHandler('info', true);
        $handlers = [$standardStreamHandler1, $standardStreamHandler2];

        $basicProcessor = BeterLoggingInitializer::createBasicProcessor();
        $processors = [$basicProcessor];

        $targetLogComponentDefinition = BeterLoggingInitializer::createMonologComponentDefinition($handlers, $processors);
        BeterLoggingInitializer::initTargetLog($targetLogComponentDefinition);

        $traceLevel = 3;
        $categories = ['application'];
        $except = [];
        $levels = ['error', 'warning', 'info'];
        $newLogComponentDefinition = BeterLoggingInitializer::createLogComponentDefinition(
            $traceLevel, $categories, $except, $levels
        );

        BeterLoggingInitializer::initLog($newLogComponentDefinition);

        \Yii::info('Info message', 'application');
        \Yii::warning('Warning message', 'application');

        return $this->render(
            'log_target',
            [
                'actionName' => __METHOD__,
                'data' => [
                    'YII_DEBUG' => YII_DEBUG,
                    'Target log component definition' => $targetLogComponentDefinition,
                    'Initial log component definition' => $logComponentDefinition,
                    'Reinitialized log component definition' => $newLogComponentDefinition,
                ]
            ]
        );
    }

    public function actionBubbling4()
    {
        $logComponentDefinition = BeterLoggingInitializer::getLogComponentDefinition();

        $logstashHandler = BeterLoggingInitializer::createLogstashHandler('info', false);
        $standardStreamHandler = BeterLoggingInitializer::createStandardStreamHandler('info', false);
        $handlers = [$logstashHandler, $standardStreamHandler];

        $basicProcessor = BeterLoggingInitializer::createBasicProcessor();
        $processors = [$basicProcessor];

        $targetLogComponentDefinition = BeterLoggingInitializer::createMonologComponentDefinition($handlers, $processors);
        BeterLoggingInitializer::initTargetLog($targetLogComponentDefinition);

        $traceLevel = 3;
        $categories = ['application', 'Beter\Yii2\Logging\*'];
        $except = [];
        $levels = ['error', 'warning', 'info'];
        $newLogComponentDefinition = BeterLoggingInitializer::createLogComponentDefinition(
            $traceLevel, $categories, $except, $levels
        );

        BeterLoggingInitializer::initLog($newLogComponentDefinition);

        \Yii::info('Info message', 'application');

        return $this->render(
            'log_target',
            [
                'actionName' => __METHOD__,
                'data' => [
                    'YII_DEBUG' => YII_DEBUG,
                    'Target log component definition' => $targetLogComponentDefinition,
                    'Initial log component definition' => $logComponentDefinition,
                    'Reinitialized log component definition' => $newLogComponentDefinition,
                ]
            ]
        );
    }

    public function actionCorrelationId()
    {
        $logComponentDefinition = BeterLoggingInitializer::getLogComponentDefinition();

        $standardStreamHandler = BeterLoggingInitializer::createStandardStreamHandler('debug', true, true, 4);
        $handlers = [$standardStreamHandler];

        $basicProcessor = BeterLoggingInitializer::createBasicProcessor();
        $correlationIdProcessor = BeterLoggingInitializer::createCorrelationIdProcessor();
        $processors = [$basicProcessor, $correlationIdProcessor];

        $targetLogComponentDefinition = BeterLoggingInitializer::createMonologComponentDefinition($handlers, $processors);
        BeterLoggingInitializer::initTargetLog($targetLogComponentDefinition);

        $traceLevel = 3;
        $categories = ['application'];
        $except = [];
        $levels = ['error', 'warning', 'info', 'trace'];
        $newLogComponentDefinition = BeterLoggingInitializer::createLogComponentDefinition(
            $traceLevel, $categories, $except, $levels
        );

        BeterLoggingInitializer::initLog($newLogComponentDefinition);

        \Yii::debug('Debug message', 'application');
        \Yii::info('Info message', 'application');
        \Yii::warning('Warning message', 'application');
        \Yii::error('Error message', 'application');

        \Yii::debug(new \Exception('Debug exception'), 'application');
        \Yii::info(new \Exception('Info exception'), 'application');
        \Yii::warning(new \Exception('Info exception'), 'application');
        \Yii::error(new \Exception('Info exception'), 'application');

        return $this->render(
            'log_target',
            [
                'actionName' => __METHOD__,
                'data' => [
                    'YII_DEBUG' => YII_DEBUG,
                    'Target log component definition' => $targetLogComponentDefinition,
                    'Initial log component definition' => $logComponentDefinition,
                    'Reinitialized log component definition' => $newLogComponentDefinition,
                ]
            ]
        );
    }

    public function actionContext()
    {
        $logComponentDefinition = BeterLoggingInitializer::getLogComponentDefinition();

        $logstashHandler = BeterLoggingInitializer::createLogstashHandler('debug', true, 'yii2-beter-logging-logstash', 5044);
        $standardStreamHandler = BeterLoggingInitializer::createStandardStreamHandler('debug', true);
        $handlers = [$logstashHandler, $standardStreamHandler];

        $basicProcessor = BeterLoggingInitializer::createBasicProcessor();
        $processors = [$basicProcessor];

        $targetLogComponentDefinition = BeterLoggingInitializer::createMonologComponentDefinition($handlers, $processors);
        BeterLoggingInitializer::initTargetLog($targetLogComponentDefinition);

        $traceLevel = 3;
        $categories = [];
        $except = [];
        $levels = ['error', 'warning', 'info', 'trace'];
        $newLogComponentDefinition = BeterLoggingInitializer::createLogComponentDefinition(
            $traceLevel, $categories, $except, $levels
        );

        BeterLoggingInitializer::initLog($newLogComponentDefinition);

        $context = [
            'field1' => 'value1',
            'field2' => [
                'value21',
                true,
                false
            ],
            'field3' => [
                'field4' => [
                    1, 2.0, new \stdClass(), new \Exception('lol')
                ]
            ]
        ];

        \Yii::debug('Debug message', 'application', $context);
        \Yii::info('Info message', 'application', $context);
        \Yii::warning('Warning message', 'application', $context);
        \Yii::error('Error message', 'application', $context);

        \Yii::debug(
            (new ExceptionWithContext('Debug exception'))->setContext(['k1' => 'v1']),
            'application',
            $context
        );
        \Yii::info(
            (new ExceptionWithContext('Info exception'))->setContext(['k2' => 'v2']),
            'application',
            $context
        );
        \Yii::warning(
            (new ExceptionWithContext('Warning exception'))->setContext(['k3' => 'v3']),
            'application',
            $context
        );
        \Yii::error(
            (new ExceptionWithContext('Error exception'))->setContext(['k4' => 'v4']),
            'application',
            $context
        );

        return $this->render(
            'log_target',
            [
                'actionName' => __METHOD__,
                'data' => [
                    'YII_DEBUG' => YII_DEBUG,
                    'Target log component definition' => $targetLogComponentDefinition,
                    'Initial log component definition' => $logComponentDefinition,
                    'Reinitialized log component definition' => $newLogComponentDefinition,
                ]
            ]
        );
    }

    public function actionHandlerStats()
    {
        $logComponentDefinition = BeterLoggingInitializer::getLogComponentDefinition();

        $logstashHandler = BeterLoggingInitializer::createLogstashHandler('debug', true, 'yii2-beter-logging-logstash', 5555);
        $standardStreamHandler = BeterLoggingInitializer::createStandardStreamHandler('debug', true);
        $handlers = [$logstashHandler, $standardStreamHandler];

        $basicProcessor = BeterLoggingInitializer::createBasicProcessor();
        $processors = [$basicProcessor];

        $targetLogComponentDefinition = BeterLoggingInitializer::createMonologComponentDefinition($handlers, $processors);
        BeterLoggingInitializer::initTargetLog($targetLogComponentDefinition);

        $traceLevel = 0;
        $categories = [];
        $except = [];
        $levels = ['error', 'warning', 'info', 'trace'];
        $newLogComponentDefinition = BeterLoggingInitializer::createLogComponentDefinition(
            $traceLevel, $categories, $except, $levels
        );

        BeterLoggingInitializer::initLog($newLogComponentDefinition);

        \Yii::debug('Debug message', 'application');
        \Yii::info('Info message', 'application');
        \Yii::warning('Warning message', 'application');
        \Yii::error('Error message', 'application');

        /** @var \Beter\Yii2\Logging\MonologComponent $monologComponent */
        $monologComponent = \Yii::$app->get(BeterLoggingInitializer::TARGET_LOG_COMPONENT);
        $stats = [];

        foreach ($monologComponent->getStats() as $logChannelName => $channelHandlers) {
            $stats[$logChannelName] = [];
            /** @var \Beter\Yii2\Logging\Handler\Stats $handlerStats */
            foreach ($channelHandlers as $handlerName => $handlerStats) {
                $execTimes = [];
                foreach ($handlerStats->getHandleExecTimes() as $value) {
                    $execTimes[] = $value;
                }

                $stats[$logChannelName][$handlerName] = [
                    'amountOfFailedHandleCalls' => $handlerStats->getAmountOfFailedHandleCalls(),
                    'amountOfSuccessfulHandleCalls' => $handlerStats->getAmountOfSuccessfulHandleCalls(),
                    'amountOfDequeuedExecTimes' => $handlerStats->getAmountOfDequeuedExecTimes(),
                    'execTimes' => $execTimes,
                ];
            }
        }

        return $this->render(
            'log_target',
            [
                'actionName' => __METHOD__,
                'data' => [
                    'YII_DEBUG' => YII_DEBUG,
                    'stats' => $stats,
                    'Target log component definition' => $targetLogComponentDefinition,
                    'Initial log component definition' => $logComponentDefinition,
                    'Reinitialized log component definition' => $newLogComponentDefinition,
                ]
            ]
        );
    }
}

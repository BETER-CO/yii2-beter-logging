<?php

namespace app\commands;

use Beter\Yii2BeterLogging\ExceptionWithContextInterface;
use yii\console\Controller;
use yii\console\ExitCode;
use app\helpers\BeterLoggingInitializer;
use Beter\Yii2BeterLogging\EnvVarSettings;
use app\exception\ExceptionWithTrait;
use app\exception\ExceptionWithContext;

class BeterLoggingController extends Controller
{

    public function actionIndex()
    {
        // env settings aren't set, using default setting
        $useColors = EnvVarSettings::colorSettingEnabled() ?? true;
        $machineReadableFormat = EnvVarSettings::machineReadableSettingEnabled() ?? false;
        $logstashEnabled = EnvVarSettings::logstashSettingEnabled() ?? false;

        $handlers = [];
        if ($logstashEnabled) {
            $handlers[] = BeterLoggingInitializer::createLogstashHandler(
                'debug', true, 'yii2-beter-logging-logstash', 5044
            );
        }

        if ($machineReadableFormat) {
            $handlers[] = BeterLoggingInitializer::createProductionStandardStreamHandler('debug', true);
        } else {
            $handlers[] = BeterLoggingInitializer::createStandardStreamHandler('debug', true, $useColors, 4);
        }


        $processors = [BeterLoggingInitializer::createBasicProcessor()];

        $targetLogComponentDefinition = BeterLoggingInitializer::createMonologComponentDefinition($handlers, $processors);
        BeterLoggingInitializer::initTargetLog($targetLogComponentDefinition);

        $traceLevel = 0;
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

        \Yii::error(new \Exception('Error exception'), 'application');
        \Yii::warning(new \Exception('Warning exception'), 'application');
        \Yii::info(new \Exception('Info exception'), 'application');
        \Yii::debug(new \Exception('Debug exception'), 'application');

        return ExitCode::OK;
    }

    public function actionBubblingOffWithActiveLogstash()
    {
        // env settings aren't set, using default setting
        $useColors = EnvVarSettings::colorSettingEnabled() ?? true;
        $machineReadableFormat = EnvVarSettings::machineReadableSettingEnabled() ?? false;
        $logstashEnabled = EnvVarSettings::logstashSettingEnabled() ?? false;

        $handlers = [];
        if ($logstashEnabled) {
            $handlers[] = BeterLoggingInitializer::createLogstashHandler(
                'debug', false, 'yii2-beter-logging-logstash', 5044
            );
        }

        if ($machineReadableFormat) {
            $handlers[] = BeterLoggingInitializer::createProductionStandardStreamHandler('debug', false);
        } else {
            $handlers[] = BeterLoggingInitializer::createStandardStreamHandler('debug', false, $useColors, 4);
        }


        $processors = [BeterLoggingInitializer::createBasicProcessor()];

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

        \Yii::debug(new \Exception('Debug exception'), 'application');
        \Yii::info(new \Exception('Info exception'), 'application');
        \Yii::warning(new \Exception('Info exception'), 'application');
        \Yii::error(new \Exception('Info exception'), 'application');

        return ExitCode::OK;
    }

    public function actionBubblingOffWithInactiveLogstash()
    {
        // env settings aren't set, using default setting
        $useColors = EnvVarSettings::colorSettingEnabled() ?? true;
        $machineReadableFormat = EnvVarSettings::machineReadableSettingEnabled() ?? false;
        $logstashEnabled = EnvVarSettings::logstashSettingEnabled() ?? false;

        $handlers = [];
        if ($logstashEnabled) {
            $handlers[] = BeterLoggingInitializer::createLogstashHandler(
                'debug', false, 'yii2-beter-logging-logstash', 5555
            );
        }

        if ($machineReadableFormat) {
            $handlers[] = BeterLoggingInitializer::createProductionStandardStreamHandler('debug', false);
        } else {
            $handlers[] = BeterLoggingInitializer::createStandardStreamHandler('debug', false, $useColors, 4);
        }


        $processors = [BeterLoggingInitializer::createBasicProcessor()];

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

        \Yii::debug(new \Exception('Debug exception'), 'application');
        \Yii::info(new \Exception('Info exception'), 'application');
        \Yii::warning(new \Exception('Info exception'), 'application');
        \Yii::error(new \Exception('Info exception'), 'application');

        return ExitCode::OK;
    }

    public function actionMessagesWithContext()
    {
        // env settings aren't set, using default setting
        $useColors = EnvVarSettings::colorSettingEnabled() ?? true;
        $machineReadableFormat = EnvVarSettings::machineReadableSettingEnabled() ?? false;
        $logstashEnabled = EnvVarSettings::logstashSettingEnabled() ?? false;

        $handlers = [];
        if ($logstashEnabled) {
            $handlers[] = BeterLoggingInitializer::createLogstashHandler(
                'debug', false, 'yii2-beter-logging-logstash', 5044
            );
        }

        if ($machineReadableFormat) {
            $handlers[] = BeterLoggingInitializer::createProductionStandardStreamHandler('debug', false);
        } else {
            $handlers[] = BeterLoggingInitializer::createStandardStreamHandler('debug', false, $useColors, 4);
        }


        $processors = [
            BeterLoggingInitializer::createBasicProcessor(),
            BeterLoggingInitializer::createCorrelationIdProcessor()
        ];

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

        $context = [
            'field1' => 'value1',
            'field2' => [
                'field3' => 'value3',
                true,
                false,
                1.2,
                3,
                null
            ]
        ];

        \Yii::debug('Debug message', 'application', $context);
        \Yii::info('Info message', 'application', $context);
        \Yii::warning('Warning message', 'application', $context);
        \Yii::error('Error message', 'application', $context);

        return ExitCode::OK;
    }

    public function actionExceptionsWithContext()
    {
        // env settings aren't set, using default setting
        $useColors = EnvVarSettings::colorSettingEnabled() ?? true;
        $machineReadableFormat = EnvVarSettings::machineReadableSettingEnabled() ?? false;
        $logstashEnabled = EnvVarSettings::logstashSettingEnabled() ?? false;

        $handlers = [];
        if ($logstashEnabled) {
            $handlers[] = BeterLoggingInitializer::createLogstashHandler(
                'debug', false, 'yii2-beter-logging-logstash', 5044
            );
        }

        if ($machineReadableFormat) {
            $handlers[] = BeterLoggingInitializer::createProductionStandardStreamHandler('debug', false);
        } else {
            $handlers[] = BeterLoggingInitializer::createStandardStreamHandler('debug', false, $useColors, 4);
        }


        $processors = [
            BeterLoggingInitializer::createBasicProcessor(),
            BeterLoggingInitializer::createCorrelationIdProcessor()
        ];

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

        $targetName = BeterLoggingInitializer::TARGET_NAME;
        $exceptionContextEntriesLimit = \Yii::$app->log->targets[$targetName]->exceptionContextEntriesLimit;
        $exceptionContextMaxDepthToAnalyze = \Yii::$app->log->targets[$targetName]->exceptionContextMaxDepthToAnalyze;

        $this->stdout("-------------------\n");
        $this->stdout("Nested exceptions: no limit hits\n");
        $this->stdout("-------------------\n");

        try {
            throw (new ExceptionWithContext('Exception1'))
                ->setContext(['key1' => 'value1']);
        } catch (\Throwable $t1) {
            $e = (new ExceptionWithContext('Exception2', 0, $t1))
                ->setContext(['key2' => 'value2']);
            \Yii::error($e, __METHOD__, [
                'notAnExceptionContext' => 'notAnExceptionValue',
            ]);
        }

        $this->stdout("Nested exceptions: no limit hits, exceptions don't implement " .
            ExceptionWithContextInterface::class . "\n"
        );
        $this->stdout("-------------------\n");

        try {
            throw (new \Exception('Exception1'));
        } catch (\Throwable $t1) {
            $e = (new \Exception('Exception2', 0, $t1));
            \Yii::error($e, __METHOD__, [
                'notAnExceptionContext' => 'notAnExceptionValue',
            ]);
        }

        $this->stdout("-------------------\n");
        $this->stdout("Nested exceptions: hits exceptionContextEntriesLimit === ${exceptionContextEntriesLimit}\n");
        $this->stdout("-------------------\n");

        try {
            throw (new ExceptionWithContext('Exception1'))
                ->setContext(['key1' => 'value1']);
        } catch (\Throwable $t1) {
            try {
                throw (new ExceptionWithContext('Exception2', 0, $t1))
                    ->setContext(['key2' => 'value2']);
            } catch (\Throwable $t2) {
                try {
                    throw (new ExceptionWithContext('Exception3', 0, $t2))
                        ->setContext(['key3' => 'value3']);
                } catch (\Throwable $t3) {
                    $e = (new ExceptionWithContext('Exception4', 0, $t3))
                        ->setContext(['key4' => 'value4']);
                    \Yii::error($e, __METHOD__, [
                        'notAnExceptionContext' => 'notAnExceptionValue',
                    ]);
                }
            }
        }

        $this->stdout("-------------------\n");
        $this->stdout("Nested exceptions: hits exceptionContextMaxDepthToAnalyze === ${exceptionContextMaxDepthToAnalyze}\n");
        $this->stdout("-------------------\n");

        try {
            throw (new \Exception('Exception1'));
        } catch (\Throwable $t1) {
            try {
                throw (new ExceptionWithContext('Exception2', 0, $t1))
                    ->setContext(['exception2' => 'value2']);
            } catch (\Throwable $t2) {
                try {
                    throw (new \Exception('Exception3', 0, $t2));
                } catch (\Throwable $t3) {
                    try {
                        throw (new ExceptionWithContext('Exception4', 0, $t3))
                            ->setContext(['exception4' => 'value4']);
                    } catch (\Throwable $t4) {
                        try {
                            throw (new \Exception('Exception5', 0, $t4));
                        } catch (\Throwable $t3) {
                            $e = (new ExceptionWithContext('Exception6', 0, $t3))
                                ->setContext(['exception6' => 'value6']);
                            \Yii::error($e, __METHOD__, [
                                'notAnExceptionContext' => 'notAnExceptionValue',
                            ]);
                        }
                    }
                }
            }
        }

        return ExitCode::OK;
    }

    public function actionExceptionsWithTraits()
    {
        // env settings aren't set, using default setting
        $useColors = EnvVarSettings::colorSettingEnabled() ?? true;
        $machineReadableFormat = EnvVarSettings::machineReadableSettingEnabled() ?? false;
        $logstashEnabled = EnvVarSettings::logstashSettingEnabled() ?? false;

        $handlers = [];
        if ($logstashEnabled) {
            $handlers[] = BeterLoggingInitializer::createLogstashHandler(
                'debug', false, 'yii2-beter-logging-logstash', 5044
            );
        }

        if ($machineReadableFormat) {
            $handlers[] = BeterLoggingInitializer::createProductionStandardStreamHandler('debug', false);
        } else {
            $handlers[] = BeterLoggingInitializer::createStandardStreamHandler('debug', false, $useColors, 4);
        }


        $processors = [
            BeterLoggingInitializer::createBasicProcessor(),
            BeterLoggingInitializer::createCorrelationIdProcessor()
        ];

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

        $targetName = BeterLoggingInitializer::TARGET_NAME;
        $exceptionContextEntriesLimit = \Yii::$app->log->targets[$targetName]->exceptionContextEntriesLimit;
        $exceptionContextMaxDepthToAnalyze = \Yii::$app->log->targets[$targetName]->exceptionContextMaxDepthToAnalyze;

        $this->stdout("-------------------\n");
        $this->stdout("Nested exceptions with traits\n");
        $this->stdout("-------------------\n");

        try {
            throw (new ExceptionWithTrait('Exception1'))
                ->setContext(['key1' => 'value1']);
        } catch (\Throwable $t1) {
            $e = (new ExceptionWithTrait('Exception2', 0, $t1))
                ->setContext(['key2' => 'value2']);
            \Yii::error($e, __METHOD__, [
                'notAnExceptionContext' => 'notAnExceptionValue',
            ]);
        }


        return ExitCode::OK;
    }
}

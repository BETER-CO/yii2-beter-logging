<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\helpers\BeterLoggingInitializer;
use Beter\Yii2BeterLogging\EnvVarSettings;

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
}

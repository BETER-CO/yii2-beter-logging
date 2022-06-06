<?php

namespace Beter\Yii2BeterLogging\Handler;

interface HandlerStatsInterface {
    public function getAmountOfFailedHandleCalls(): int;
    public function getAmountOfSuccessfulHandleCalls(): int;
}

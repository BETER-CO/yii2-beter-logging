<?php

namespace Beter\Yii2BeterLogging\Handler;

interface ProcessHandleStatsInterface {
    public function getAmountOfFailedHandleCalls(): int;
    public function getAmountOfSuccessfulHandleCalls(): int;
}

<?php

namespace Beter\Yii2BeterLogging\Handler;

class Stats implements ProcessHandleStatsInterface {

    protected int $amountOfFailedHandleCalls = 0;
    protected int $amountOfSuccessfulHandleCalls = 0;

    public function __construct() {
    }

    public function getAmountOfFailedHandleCalls(): int
    {
        return $this->amountOfFailedHandleCalls;
    }

    public function getAmountOfSuccessfulHandleCalls(): int
    {
        return $this->amountOfSuccessfulHandleCalls;
    }

    public function incFailedHandleCalls(): self
    {
        $this->amountOfFailedHandleCalls++;

        return $this;
    }

    public function incSuccessfulHandleCalls(): self
    {
        $this->amountOfSuccessfulHandleCalls++;

        return $this;
    }
}

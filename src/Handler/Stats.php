<?php

namespace Beter\Yii2\Logging\Handler;

class Stats implements HandlerStatsInterface {

    protected \SplQueue $handleExecTimeQueue;

    protected int $handleExecTimeQueueMaxSize;
    protected int $handleExecTimeQueueCurrentSize = 0;
    protected int $handleExecTimeQueueDequeued;

    protected int $amountOfFailedHandleCalls = 0;
    protected int $amountOfSuccessfulHandleCalls = 0;

    public function __construct(int $execTimeQueueMaxSize = 1000) {
        if ($execTimeQueueMaxSize <= 1) {
            throw new \InvalidArgumentException('execTimeMaxQueueSize can not be less than 1');
        }

        $this->handleExecTimeQueueMaxSize = $execTimeQueueMaxSize;
        $this->handleExecTimeQueueDequeued = 0;
        $this->handleExecTimeQueue = new \SplQueue();
    }

    public function addHandleExecTime(float $execTime): self
    {
        if ($this->handleExecTimeQueueCurrentSize >= $this->handleExecTimeQueueMaxSize) {
            $this->handleExecTimeQueue->dequeue();
            $this->handleExecTimeQueueDequeued++;
        } else {
            $this->handleExecTimeQueueCurrentSize++;
        }

        $this->handleExecTimeQueue->enqueue($execTime);
        return $this;
    }

    public function getAmountOfDequeuedExecTimes(): int
    {
        return $this->handleExecTimeQueueDequeued;
    }


    public function getAmountOfFailedHandleCalls(): int
    {
        return $this->amountOfFailedHandleCalls;
    }

    public function getAmountOfSuccessfulHandleCalls(): int
    {
        return $this->amountOfSuccessfulHandleCalls;
    }

    public function getHandleExecTimes(): \SplQueue
    {
        return $this->handleExecTimeQueue;
    }

    public function hasExecTimeQueueBeenOverflowed(): int
    {
        return $this->handleExecTimeQueueDequeued !== 0;
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

<?php

namespace Beter\Yii2\Logging\Handler;

trait HandlerWithStatsTrait
{
    /**
     * Every Stats object has a queue that stores execution times for the handle method. The queue may consume a lot
     * of memory, so we need to limit queue size.
     *
     * @var int
     */
    protected int $execTimeQueueMaxSize = 1000;

    /**
     * The main purpose if this method is to prevent incorrect stats if few handlers have the same label
     *
     * @var bool
     */
    protected bool $statsDisableStatus = false;

    /**
     * Every Monolog log channel may have few different log handlers of the same type.
     * But stats for each of them are different, so there must be a label to differ them.
     *
     * @var string
     */
    protected string $label;
    protected Stats $stats;


    public function getLabel(): string
    {
        return $this->label;
    }

    public function getStats(): Stats
    {
        return $this->stats;
    }

    public function disableStats(): self
    {
        $this->statsDisableStatus = true;

        return $this;
    }

    public function initStats(): self
    {
        $this->stats = new Stats($this->execTimeQueueMaxSize);

        return $this;
    }

    public function setExecTimeQueueMaxSize(int $execTimeQueueMaxSize): self
    {
        if ($execTimeQueueMaxSize <= 1) {
            throw new \InvalidArgumentException('execTimeQueueMaxSize can not be less than 1');
        }

        $this->execTimeQueueMaxSize = $execTimeQueueMaxSize;
        return $this;
    }

    public function isStatsDisabled(): bool
    {
        return $this->statsDisableStatus;
    }

    public function setLabel(string $label): self
    {
        if (empty($label)) {
            throw new \InvalidArgumentException('label must be a non-empty string');
        }

        $this->label = $label;

        return $this;
    }
}

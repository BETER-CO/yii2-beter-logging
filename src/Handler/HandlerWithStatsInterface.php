<?php

namespace Beter\Yii2\Logging\Handler;

interface HandlerWithStatsInterface
{
    public function getLabel(): string;
    public function getStats(): Stats;
    public function disableStats(): self;
    public function isStatsDisabled(): bool;
    public function initStats(): self;
    public function setExecTimeQueueMaxSize(int $execTimeQueueMaxSize): self;
    public function setLabel(string $label): self;
}

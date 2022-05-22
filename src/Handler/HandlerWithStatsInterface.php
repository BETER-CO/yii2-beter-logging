<?php

namespace Beter\Yii2BeterLogging\Handler;

interface HandlerWithStatsInterface
{
    public function getLabel(): string;
    public function getStats(): Stats;
    public function disableStats(): self;
    public function isStatsDisabled(): bool;
    public function setLabel(string $label): self;
}

<?php

namespace Beter\Yii2BeterLogging\Handler;

interface HandlerWithHandleErrorProcessingInterface
{
    public function setHandleExceptionHandler(?callable $callback): self;
    public function setMaxHandleErrorsBeforeDisabling(int $maxAmountOfHandleErrors): self;
}

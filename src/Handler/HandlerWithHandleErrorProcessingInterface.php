<?php

namespace Beter\Yii2\Logging\Handler;

interface HandlerWithHandleErrorProcessingInterface
{
    public function setHandleExceptionHandler(?callable $callback): self;
    public function setMaxHandleErrorsBeforeDisabling(int $maxAmountOfHandleErrors): self;
}

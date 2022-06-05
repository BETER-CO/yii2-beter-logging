<?php

namespace Beter\Yii2BeterLogging;

use Beter\Yii2BeterLogging\ExceptionWithContextInterface;

trait ExceptionWithContextTrait
{
    protected array $context;

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }
}
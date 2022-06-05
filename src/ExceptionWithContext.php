<?php

namespace Beter\Yii2BeterLogging;

use Beter\Yii2BeterLogging\ExceptionWithContextInterface;

class ExceptionWithContext extends \Exception implements ExceptionWithContextInterface
{
    protected array $context;

    public function __construct($message = "", $code = 0, \Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);

        $this->context = $context;
    }

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
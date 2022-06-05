<?php

namespace Beter\Yii2BeterLogging;

interface ExceptionWithContextInterface
{
    public function getContext(): array;
    public function setContext(array $context): self;
}

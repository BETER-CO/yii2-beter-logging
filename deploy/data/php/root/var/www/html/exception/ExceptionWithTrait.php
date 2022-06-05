<?php

namespace app\exception;

use Beter\Yii2BeterLogging\ExceptionWithContextTrait;
use Beter\Yii2BeterLogging\ExceptionWithContextInterface;

class ExceptionWithTrait implements ExceptionWithContextInterface
{
    use ExceptionWithContextTrait;
}
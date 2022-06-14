<?php

namespace app\exception;

class ExceptionWithTrait extends \Exception implements \Beter\ExceptionWithContext\ExceptionWithContextInterface
{
    use \Beter\ExceptionWithContext\ExceptionWithContextTrait;
}

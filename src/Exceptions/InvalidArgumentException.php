<?php


namespace DucCnzj\Ip\Exceptions;


use Throwable;

class InvalidArgumentException extends Exception
{
    public function __construct($message = "参数验证失败", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
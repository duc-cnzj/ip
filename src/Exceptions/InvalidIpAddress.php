<?php

namespace DucCnzj\Ip\Exceptions;

use Throwable;

class InvalidIpAddress extends Exception
{
    public function __construct($message = 'ip 地址格式不正确', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

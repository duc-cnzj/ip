<?php

namespace DucCnzj\Ip\Exceptions;

use Throwable;

class ServerErrorException extends Exception
{
    public function __construct($message = '获取 ip 信息失败', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

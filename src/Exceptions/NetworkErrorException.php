<?php


namespace DucCnzj\Ip\Exceptions;


use Throwable;

class NetworkErrorException extends Exception
{
    public function __construct($message = "网络不通畅", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
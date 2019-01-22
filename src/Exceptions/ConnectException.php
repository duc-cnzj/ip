<?php

namespace DucCnzj\Ip\Exceptions;

class ConnectException extends \GuzzleHttp\Exception\ConnectException implements BreakLoopExceptionImp
{
}

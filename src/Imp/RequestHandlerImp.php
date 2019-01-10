<?php

namespace DucCnzj\Ip\Imp;

use GuzzleHttp\ClientInterface;

interface RequestHandlerImp
{
    public function send(array $providers, string $ip);

    public function getClient(): ClientInterface;

    public function setTryTimes(int $times);

    public function getTryTimes(): int;
}

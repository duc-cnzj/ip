<?php

namespace DucCnzj\Ip\Imp;

use GuzzleHttp\ClientInterface;

interface IpImp
{
    public function send(ClientInterface $client, string $ip): array;
}

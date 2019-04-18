<?php

namespace DucCnzj\Ip\Imp;

use GuzzleHttp\ClientInterface;

/**
 * Interface IpImp
 * @package DucCnzj\Ip\Imp
 */
interface IpImp
{
    /**
     * @param ClientInterface $client
     * @param string $ip
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function send(ClientInterface $client, string $ip): array;
}

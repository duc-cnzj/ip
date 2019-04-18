<?php

namespace DucCnzj\Ip\Imp;

use GuzzleHttp\ClientInterface;

/**
 * Interface SendRequestImp
 *
 * @package DucCnzj\Ip\Imp
 */
interface SendRequestImp
{
    /**
     * @param array $providers
     * @param string $ip
     *
     * @return mixed
     *
     * @author duc <1025434218@qq.com>
     */
    public function send(array $providers, string $ip);

    /**
     * @return ClientInterface
     *
     * @author duc <1025434218@qq.com>
     */
    public function getClient(): ClientInterface;

    /**
     * @param int $times
     *
     * @return mixed
     *
     * @author duc <1025434218@qq.com>
     */
    public function setTryTimes(int $times);

    /**
     * @return int
     *
     * @author duc <1025434218@qq.com>
     */
    public function getTryTimes(): int;

    /**
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function getErrors(): array ;
}

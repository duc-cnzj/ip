<?php

namespace DucCnzj\Ip;

use GuzzleHttp\Client;
use DucCnzj\Ip\Imp\IpImp;
use GuzzleHttp\ClientInterface;
use DucCnzj\Ip\Imp\RequestHandlerImp;
use DucCnzj\Ip\Exceptions\ServerErrorException;
use DucCnzj\Ip\Exceptions\InvalidArgumentException;

class RequestHandler implements RequestHandlerImp
{
    /**
     * @var ClientInterface|null
     */
    protected $client;

    /**
     * @var int
     */
    public $tryTimes = 3;

    /**
     * @param int $tryTimes
     *
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function setTryTimes(int $tryTimes)
    {
        $this->tryTimes = $tryTimes;

        return $this;
    }

    /**
     * @return ClientInterface
     *
     * @author duc <1025434218@qq.com>
     */
    public function getClient(): ClientInterface
    {
        return is_null($this->client)
            ? $this->client = new Client()
            : $this->client;
    }

    /**
     * @param array  $providers
     * @param string $ip
     *
     * @return array
     * @throws ServerErrorException
     *
     * @author duc <1025434218@qq.com>
     */
    public function send($providers, $ip)
    {
        foreach ($providers as $name => $provider) {
            for ($time = 1; $time <= $this->tryTimes; $time++) {
                try {
                    /** @var IpImp $provider */
                    return array_merge($provider->send($this->getClient(), $ip), [
                        'provider' => $name,
                        'success'  => 1,
                    ]);
                } catch (ServerErrorException $e) {
                    continue;
                } catch (InvalidArgumentException $exception) {
                    continue 2;
                }
            }
        }

        throw new ServerErrorException();
    }
}

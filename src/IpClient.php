<?php

namespace DucCnzj\Ip;

use GuzzleHttp\Client;
use DucCnzj\Ip\Exceptions\HttpException;
use DucCnzj\Ip\Exceptions\InvalidIpAddress;

class IpClient
{
    /**
     * @var string
     */
    protected $ipAddress;

    /**
     * @var string
     */
    protected $url = 'http://ip.taobao.com/service/getIpInfo.php';

    /**
     * @var array
     */
    protected $guzzleOptions = [];

    public function __construct(string $ipAddress = '')
    {
        $this->ipAddress = $ipAddress;
    }

    public function getIpInfo()
    {
        $b = preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $this->ipAddress);
        if (! $b) {
            throw new InvalidIpAddress;
        }

        try {
            $response = $this->getHttpClient()->get(
                $this->url,
                [
                   'query' => ['ip' => $this->ipAddress],
                ]
            );
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }

        return $response->getBody()->getContents();
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    /**
     * @param string $ipAddress
     *
     * @return $this
     */
    public function setIpAddress(string $ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }
}

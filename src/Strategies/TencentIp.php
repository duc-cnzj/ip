<?php

namespace DucCnzj\Ip\Strategies;

use DucCnzj\Ip\Imp\IpImp;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use DucCnzj\Ip\Exceptions\ServerErrorException;

/**
 *
 * Class TencentIp
 *
 * @package DucCnzj\Ip\Strategies
 */
class TencentIp implements IpImp
{
    /**
     * @var string
     */
    public $url = 'https://apis.map.qq.com/ws/location/v1/ip';

    /**
     * @var string
     */
    public $key;

    /**
     * TencentIp constructor.
     *
     * @param $config
     */
    public function __construct($config = [])
    {
        $this->setConfig($config);
    }

    /**
     * @param array|string $config
     *
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function setConfig($config)
    {
        if (is_array($config)) {
            $this->key = isset($config['key']) ? $config['key'] : '';
        } else {
            $this->key = $config;
        }

        return $this;
    }

    /**
     * @param ClientInterface $httpClient
     * @param string          $ip
     *
     * @return array
     * @throws ServerErrorException
     *
     * @author duc <1025434218@qq.com>
     */
    public function send(ClientInterface $httpClient, string $ip): array
    {
        try {
            $originalStr = $httpClient->request('get', $this->url . '?ip=' . $ip . '&key=' . $this->getKey())
                ->getBody();

            $result = json_decode($originalStr, true);

            if ($result['status'] !== 0) {
                throw new ServerErrorException($result['message']);
            }

            $data['ip'] = $ip;

            $data = $this->formatResult($data, $result);

            return $data;
        } catch (ServerException $e) {
            throw new ServerErrorException();
        } catch (ClientException $e) {
            throw new ServerErrorException();
        }
    }

    /**
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param array $result
     * @param array $data
     *
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function formatResult(array $data, array $result)
    {
        $data['country'] = $result['result']['ad_info']['nation'];
        $data['region'] = $result['result']['ad_info']['province'];
        $data['city'] = $result['result']['ad_info']['city'];
        $data['address'] = $data['country'] . $data['region'] . $data['city'];
        $data['point_x'] = $result['result']['location']['lng'];
        $data['point_y'] = $result['result']['location']['lat'];
        $data['isp'] = '';

        return $data;
    }
}

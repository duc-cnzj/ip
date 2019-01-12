<?php

namespace DucCnzj\Ip\Strategies;

use DucCnzj\Ip\Imp\IpImp;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use DucCnzj\Ip\Exceptions\ServerErrorException;
use DucCnzj\Ip\Exceptions\InvalidArgumentException;

class BaiduIp implements IpImp
{
    /**
     * @var string
     */
    protected $ak;

    /**
     * @var string
     */
    protected $url = 'http://api.map.baidu.com/location/ip';

    /**
     * BaiduIp constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->setConfig($config);
    }

    /**
     * @param array|string $config
     *
     * @return IpImp
     *
     * @author duc <1025434218@qq.com>
     */
    public function setConfig($config): IpImp
    {
        if (is_array($config)) {
            $this->ak = isset($config['ak']) ? $config['ak'] : '';
        } else {
            $this->ak = $config;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAk()
    {
        return $this->ak;
    }

    /**
     * @param ClientInterface $client
     * @param string          $ip
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws ServerErrorException
     *
     * @author duc <1025434218@qq.com>
     */
    public function send(ClientInterface $client, string $ip): array
    {
        try {
            $originalStr = $client->request('get', $this->url . '?ip=' . $ip . '&ak=' . $this->getAk())
                ->getBody();

            $result = json_decode($originalStr, true);

            if ($result['status'] !== 0) {
                throw new InvalidArgumentException($result['message']);
            }

            $data = ['ip' => $ip];
            $data = $this->formatResult($data, $result);

            return $data;
        } catch (ServerException $e) {
            throw new ServerErrorException($e->getMessage());
        } catch (ClientException $e) {
            throw new ServerErrorException($e->getMessage());
        }
    }

    /**
     * @param array $data
     * @param array $result
     *
     * @return mixed
     *
     * @author duc <1025434218@qq.com>
     */
    public function formatResult(array $data, array $result)
    {
        $data['country'] = '中国';
        $data['isp'] = '';
        $data['address'] = $data['country'] . $result['content']['address'];
        $data['region'] = $result['content']['address_detail']['province'];
        $data['city'] = $result['content']['address_detail']['city'];
        $data['point_x'] = $result['content']['point']['x'];
        $data['point_y'] = $result['content']['point']['y'];

        return $data;
    }
}

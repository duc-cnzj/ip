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
     * @var
     */
    protected $ak;

    /**
     * @var string
     */
    protected $url = 'http://api.map.baidu.com/location/ip';

    public function __construct($config = [])
    {
        $this->setConfig($config);
    }

    /**
     * @param array $config
     *
     * @return IpImp
     *
     * @author duc <1025434218@qq.com>
     */
    public function setConfig($config = []): IpImp
    {
        $this->ak = isset($config['ak']) ? $config['ak'] : '';

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
            $data = [];
            $originalStr = $client->request('get', $this->url . '?ip=' . $ip . '&ak=' . $this->getAk())
                ->getBody();

            $result = json_decode($originalStr, true);

            if ($result['status'] !== 0) {
                echo '百度获取不到了';
                throw new InvalidArgumentException($result['message']);
            }

            $data['ip'] = $ip;
            $data['country'] = '中国';
            $data['isp'] = '';
            $data['address'] = $data['country'] . $result['content']['address'];
            $data['region'] = $result['content']['address_detail']['province'];
            $data['city'] = $result['content']['address_detail']['city'];
            $data['point_x'] = $result['content']['point']['x'];
            $data['point_y'] = $result['content']['point']['y'];

            return $data;
        } catch (ServerException | ClientException $e) {
            throw new ServerErrorException();
        }
    }
}

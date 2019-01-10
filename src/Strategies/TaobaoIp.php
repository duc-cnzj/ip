<?php

namespace DucCnzj\Ip\Strategies;

use DucCnzj\Ip\Imp\IpImp;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use DucCnzj\Ip\Exceptions\ServerErrorException;

class TaobaoIp implements IpImp
{
    protected $url = 'http://ip.taobao.com/service/getIpInfo.php';

    /**
     * @param ClientInterface $httpClient
     *
     * @param string          $ip
     *
     * @return array
     * @throws ServerErrorException
     * @author duc <1025434218@qq.com>
     */
    public function send(ClientInterface $httpClient, string $ip):array
    {
        try {
            $originalStr = $httpClient->request('get', $this->url . '?ip=' . $ip)
                ->getBody();

            $result = json_decode($originalStr, true);

            if ($result['code'] !== 0) {
                throw new ServerErrorException();
            }

            $data['ip'] = $ip;

            $data = $this->formatResult($data, $result);

            return $data;
        } catch (ServerException | ClientException $e) {
            throw new ServerErrorException();
        }
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
        $data['country'] = $result['data']['country'];
        $data['region'] = $result['data']['region'];
        $data['city'] = $result['data']['city'];
        $data['address'] = $data['country'] . $data['region'] . $data['city'];
        $data['point_x'] = '';
        $data['point_y'] = '';
        $data['isp'] = $result['data']['isp'];

        return $data;
    }
}

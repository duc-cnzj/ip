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
     * @param ClientInterface $client
     *
     * @param string          $ip
     *
     * @return array
     * @throws ServerErrorException
     * @author duc <1025434218@qq.com>
     */
    public function send(ClientInterface $client, string $ip):array
    {
        try {
            $originalStr = $client->request('get', $this->url . '?ip=' . $ip)
                ->getBody();

            echo '淘宝获取成功';
        } catch (ServerException | ClientException $e) {
            throw new ServerErrorException();
        }

        $result = json_decode($originalStr, true);
        $data['ip'] = $ip;
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

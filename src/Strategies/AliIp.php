<?php

namespace DucCnzj\Ip\Strategies;

use DucCnzj\Ip\Imp\IpImp;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use DucCnzj\Ip\Exceptions\AnalysisException;
use DucCnzj\Ip\Exceptions\ServerErrorException;
use DucCnzj\Ip\Exceptions\UnauthorizedException;

class AliIp implements IpImp
{
    /**
     * @var string
     */
    protected $appCode;

    protected $url = 'http://iploc.market.alicloudapi.com/v3/ip';

    public function __construct($config = [])
    {
        $this->setConfig($config);
    }

    public function setConfig($config = []): IpImp
    {
        $this->appCode = isset($config['app_code']) ? $config['app_code'] : '';

        return $this;
    }

    /**
     * @return string
     *
     * @author duc <1025434218@qq.com>
     */
    public function getAppCode()
    {
        return $this->appCode;
    }

    public function send(ClientInterface $client, string $ip): array
    {
        try {
            $originalStr = $client->request(
                'get',
                $this->url . '?ip=' . $ip,
                [
                'headers' => [
                    'Authorization' => "APPCODE {$this->getAppCode()}",
                ],
            ]
            )->getBody();

            $result = json_decode($originalStr, true);

            if ($result['rectangle'] === []) {
                throw new AnalysisException('ip 地址解析失败');
            }

            $data = ['ip' => $ip];
            $data = $this->formatResult($data, $result);

            return $data;
        } catch (ServerException $e) {
            throw new ServerErrorException($e->getMessage());
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 401) {
                throw new UnauthorizedException($e->getMessage());
            }

            throw new ServerErrorException($e->getMessage());
        }
    }

    public function formatResult($data, $result)
    {
        // 116.0119343,39.66127144;116.7829835,40.2164962
        $arr = explode(';', $result['rectangle']);
        $rectangle = explode(',', $arr[0]);
        $pointX = $rectangle[0];
        $pointY = $rectangle[1];
        $data['city'] = $result['city'];
        $data['region'] = $result['province'];
        $data['country'] = '中国';
        $data['point_x'] = $pointX;
        $data['point_y'] = $pointY;
        $data['isp'] = '';
        $data['address'] = $data['country'] . $data['region'] . $data['city'];

        return $data;
    }
}

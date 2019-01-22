<?php

namespace DucCnzj\Ip\Strategies;

use DucCnzj\Ip\Imp\IpImp;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use DucCnzj\Ip\Exceptions\AnalysisException;
use DucCnzj\Ip\Exceptions\BreakLoopException;
use DucCnzj\Ip\Exceptions\ServerErrorException;

class AliIp implements IpImp
{
    /**
     * @var string
     */
    protected $appCode;

    /**
     * @var string
     */
    protected $url = 'http://iploc.market.alicloudapi.com/v3/ip';

    /**
     * AliIp constructor.
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
            $this->appCode = isset($config['app_code']) ? $config['app_code'] : '';
        } else {
            $this->appCode = $config;
        }

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

    /**
     * @param ClientInterface $client
     * @param string          $ip
     *
     * @return array
     * @throws AnalysisException
     * @throws BreakLoopException
     * @throws ServerErrorException
     *
     * @author duc <1025434218@qq.com>
     */
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
            throw new BreakLoopException($e->getMessage());
        }
    }

    /**
     * @param array $data
     * @param array $result
     *
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function formatResult(array $data, array $result)
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

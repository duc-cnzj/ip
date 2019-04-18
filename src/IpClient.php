<?php

namespace DucCnzj\Ip;

use DucCnzj\Ip\Imp\DataMapImp;
use DucCnzj\Ip\Imp\CacheStoreImp;
use DucCnzj\Ip\Imp\RequestHandlerImp;
use DucCnzj\Ip\Traits\HandleProvider;
use DucCnzj\Ip\Exceptions\InvalidIpAddress;
use DucCnzj\Ip\Exceptions\ServerErrorException;
use DucCnzj\Ip\Exceptions\IpProviderClassNotExistException;

/**
 * @method string getCity()
 * @method string getCountry()
 * @method string getAddress()
 * @method string getRegion()
 *
 * Class IpClient
 *
 * @package DucCnzj\Ip
 */
class IpClient
{
    use HandleProvider;

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var DataMapImp
     */
    protected $dataMapper;

    /**
     * @var RequestHandlerImp|null
     */
    protected $requestHandler;

    /**
     * @param int $times
     *
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function try(int $times)
    {
        $this->requestHandler = $this->getRequestHandler()->setTryTimes($times);

        return $this;
    }

    /**
     * @param string $ip
     *
     * @throws InvalidIpAddress
     *
     * @author duc <1025434218@qq.com>
     */
    public function checkIp(string $ip)
    {
        $b = preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip);

        if (! $b) {
            throw new InvalidIpAddress;
        }
    }

    /**
     * @param string $msg
     *
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    protected function responseWithError(string $msg)
    {
        return [
            'success' => 0,
            'message' => $msg,
        ];
    }

    /**
     * @return DataMapper|DataMapImp|NullDataMapper
     *
     * @throws IpProviderClassNotExistException
     * @throws \Exception
     * @author duc <1025434218@qq.com>
     */
    public function getDataMapper()
    {
        $response = $this->getOriginalInfo();

        if (! $response['success']) {
            return (new NullDataMapper())->setInfo(['ip' => $this->getIp()]);
        }

        if (! $this->dataMapper) {
            $this->dataMapper = new DataMapper();

            return $this->dataMapper->setInfo($response);
        }

        return $this->dataMapper->setInfo($response);
    }

    /**
     * @return RequestHandlerImp|RequestHandler|null
     *
     * @author duc <1025434218@qq.com>
     */
    public function getRequestHandler()
    {
        if (! $this->requestHandler) {
            return $this->requestHandler = new RequestHandler();
        }

        return $this->requestHandler;
    }

    /**
     * @return array|mixed
     *
     * @throws IpProviderClassNotExistException
     * @throws \Exception
     * @author duc <1025434218@qq.com>
     */
    public function getOriginalInfo()
    {
        try {
            $result = $this->getRequestHandler()
                ->send($this->resolveProviders(), $this->getIp());
        } catch (ServerErrorException $e) {
            return $this->responseWithError($e->getMessage());
        } catch (\RuntimeException $exception) {
            throw $exception;
        }

        return $result;
    }

    /**
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function getErrors(): array
    {
        return $this->getRequestHandler()->getErrors();
    }

    /**
     * @return string
     * @throws \Exception
     *
     * @author duc <1025434218@qq.com>
     */
    public function getIp()
    {
        if (! $this->ip) {
            throw new \Exception('请先设置 ip');
        }

        return $this->ip;
    }

    /**
     * @return CacheStore|CacheStoreImp
     *
     * @author duc <1025434218@qq.com>
     */
    public function getCacheStore()
    {
        return $this->getRequestHandler()->getCacheStore();
    }

    /**
     * @param string $ip
     *
     * @return $this
     *
     * @throws InvalidIpAddress
     * @author duc <1025434218@qq.com>
     */
    public function setIp(string $ip)
    {
        $this->checkIp($ip);

        $this->ip = $ip;

        return $this;
    }

    /**
     * @param DataMapImp $dataMapper
     *
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function setDataMapper(DataMapImp $dataMapper)
    {
        $this->dataMapper = $dataMapper;

        return $this;
    }

    /**
     * @param RequestHandlerImp $requestHandler
     *
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function setRequestHandler(RequestHandlerImp $requestHandler)
    {
        $this->requestHandler = $requestHandler;

        return $this;
    }

    /**
     * @param CacheStoreImp $cacheStore
     *
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function setCacheStore(CacheStoreImp $cacheStore)
    {
        $this->requestHandler = $this->getRequestHandler()->setCacheStore($cacheStore);

        return $this;
    }

    /**
     * @param string $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     *
     * @author duc <1025434218@qq.com>
     */
    public function __call(string $name, $arguments)
    {
        return $this->getDataMapper()->{$name}(...$arguments);
    }

    /**
     * @param string $name
     * @return mixed|string|null
     * @throws \Exception
     *
     * @author duc <1025434218@qq.com>
     */
    public function __get(string $name)
    {
        return $this->getDataMapper()->{$name};
    }
}

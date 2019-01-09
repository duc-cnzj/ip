<?php

namespace DucCnzj\Ip;

use DucCnzj\Ip\Imp\IpImp;
use DucCnzj\Ip\Imp\DataMapImp;
use DucCnzj\Ip\Imp\CacheStoreImp;
use DucCnzj\Ip\Imp\RequestHandlerImp;
use DucCnzj\Ip\Exceptions\InvalidIpAddress;
use DucCnzj\Ip\Exceptions\NetworkErrorException;
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
    /**
     * @var string
     */
    protected $ip;

    /**
     * @var array
     */
    protected $providerConfig = [];

    /**
     * @var array ['baidu', 'taobao']
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var null|DataMapImp
     */
    protected $dataMapper = null;

    /**
     * @var null|CacheStoreImp
     */
    protected $cacheStore = null;

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
    public function checkIp(string $ip): void
    {
        $b = preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip);

        if (! $b) {
            throw new InvalidIpAddress;
        }
    }

    /**
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function resolveProviders()
    {
        foreach ($this->getProviders() as $provider) {
            $this->instances[$provider] = $this->createProvider($provider);
        }

        return $this->instances;
    }

    /**
     * @param $provider
     *
     * @return IpImp
     *
     * @throws IpProviderClassNotExistException
     *
     * @author duc <1025434218@qq.com>
     */
    protected function createProvider($provider)
    {
        $config = $this->getProviderConfig($provider);

        $shortName = ucfirst(strtolower($provider)) . 'Ip';

        $class = __NAMESPACE__ . "\Strategies\\{$shortName}";

        if (! class_exists($class)) {
            throw new IpProviderClassNotExistException("{$class} 不存在");
        }

        return new $class($config);
    }

    /**
     * @param $msg
     *
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    protected function responseWithError($msg)
    {
        return [
            'success' => 0,
            'message' => $msg,
        ];
    }

    /**
     * @return array|DataMapper|DataMapImp|null
     *
     * @author duc <1025434218@qq.com>
     */
    public function getDataMapper()
    {
        if (! $this->dataMapper) {
            $response = $this->getOriginalInfo();

            return $this->dataMapper = new DataMapper($response);
        }

        return $this->dataMapper;
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
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function getOriginalInfo()
    {
        if ($info = $this->getCacheStore()->get($this->getIp())) {
            return $info;
        }

        $result = $this->getRequestHandler()
            ->send($this->resolveProviders(), $this->getIp());

        $this->cacheStore->put($this->getIp(), $result);

        return $result;
    }

    /**
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function getProviders()
    {
        if (count($this->providers) === 0) {
            return $this->providers = $this->getDefaultProviders();
        }

        return $this->providers;
    }

    /**
     * @return CacheStore
     *
     * @author duc <1025434218@qq.com>
     */
    protected function getDefaultCacheDriver()
    {
        return new CacheStore();
    }

    /**
     * @return string
     * @throws \Exception
     *
     * @author duc <1025434218@qq.com>
     */
    protected function getIp()
    {
        if (is_null($this->ip)) {
            throw new \Exception('请先设置 ip');
        }

        return $this->ip;
    }

    /**
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    protected function getDefaultProviders()
    {
        return [
            'baidu',
            'taobao',
        ];
    }

    /**
     * @param string $provider
     *
     * @return array
     */
    public function getProviderConfig(string $provider): array
    {
        if (! isset($this->providerConfig[$provider])) {
            return [];
        }

        return $this->providerConfig[$provider];
    }

    /**
     * @return CacheStore|CacheStoreImp|null
     *
     * @author duc <1025434218@qq.com>
     */
    public function getCacheStore()
    {
        if ($this->cacheStore instanceof CacheStoreImp) {
            return $this->cacheStore;
        }

        return $this->cacheStore = $this->getDefaultCacheDriver();
    }

    /**
     * @param string $ip
     *
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function setIp(string $ip)
    {
        $this->checkIp($ip);

        $this->ip = $ip;

        return $this;
    }

    /**
     * @param string $provider
     *
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function useProvider(string $provider)
    {
        $this->providers[] = $provider;

        $this->providers = array_unique($this->providers);

        return $this;
    }

    /**
     * @param       $provider
     * @param array $config
     *
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function setProviderConfig($provider, array $config)
    {
        $this->providerConfig[$provider] = $config;

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
        $this->cacheStore = $cacheStore;

        return $this;
    }

    /**
     * @param string $provider
     * @param IpImp  $instance
     *
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function bound(string $provider, IpImp $instance)
    {
        $this->instances[$provider] = $instance;

        return $this;
    }

    public function getInstanceByName(string $name)
    {
        return isset($this->instances[$name]) ? $this->instances[$name] : null;
    }



    /**
     * @param string $name
     * @param $arguments
     *
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function __call(string $name, $arguments)
    {
        try {
            if ($this->getDataMapper()->hasInfo()) {
                return $this->getDataMapper()->{$name}(...$arguments);
            } else {
                return $this->responseWithError('请先获取数据');
            }
        } catch (NetworkErrorException $e) {
            return $this->responseWithError($e->getMessage());
        }
    }

    /**
     * @param string $name
     *
     * @return mixed|string
     *
     * @author duc <1025434218@qq.com>
     */
    public function __get(string $name)
    {
        return $this->getDataMapper()->{$name};
    }
}

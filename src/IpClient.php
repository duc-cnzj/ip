<?php

namespace DucCnzj\Ip;

use DucCnzj\Ip\Imp\IpImp;
use DucCnzj\Ip\Imp\DataMapImp;
use DucCnzj\Ip\Imp\CacheStoreImp;
use DucCnzj\Ip\Imp\RequestHandlerImp;
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
    /**
     * @var string
     */
    protected $ip;

    /**
     * @var array
     */
    protected $providerConfig = [];

    /**
     * @var array|null ['baidu', 'taobao']
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var DataMapImp
     */
    protected $dataMapper;

    /**
     * @var null|CacheStoreImp
     */
    protected $cacheStore;

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
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function resolveProviders()
    {
        foreach ($this->getProviders() as $provider) {
            if (! isset($this->instances[$provider])) {
                $this->instances[$provider] = $this->createProvider($provider);
            }
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
    public function createProvider($provider)
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
    public function responseWithError($msg)
    {
        return [
            'success' => 0,
            'message' => $msg,
        ];
    }

    /**
     * @return DataMapper|DataMapImp|NullDataMapper
     *
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
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function getOriginalInfo()
    {
        if ($info = $this->getCacheStore()->get($this->getIp())) {
            return $info;
        }

        try {
            $result = $this->getRequestHandler()
                ->send($this->resolveProviders(), $this->getIp());
        } catch (ServerErrorException $e) {
            return $this->responseWithError($e->getMessage());
        } catch (\Exception $e) {
            return $this->responseWithError($e->getMessage());
        }

        $this->getCacheStore()->put($this->getIp(), $result);

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
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function getProviders()
    {
        if (is_null($this->providers)) {
            return [];
        }

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
    public function getDefaultCacheDriver()
    {
        return new CacheStore();
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
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function getDefaultProviders()
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
     * @return CacheStore|CacheStoreImp
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
     * @param string[] ...$provider
     *
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function useProvider(string ...$provider)
    {
        $providers = array_merge($this->providers ?? [], array_filter($provider));
        $this->providers = array_unique($providers);

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

    /**
     * @param string[] ...$provider
     *
     * @return IpClient
     *
     * @author duc <1025434218@qq.com>
     */
    public function use(string ...$provider)
    {
        return $this->useProvider(...$provider);
    }

    /**
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function clearUse()
    {
        $this->providers = null;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     *
     * @author duc <1025434218@qq.com>
     */
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
        return $this->getDataMapper()->{$name}(...$arguments);
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

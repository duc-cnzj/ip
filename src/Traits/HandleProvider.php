<?php

namespace DucCnzj\Ip\Traits;

use DucCnzj\Ip\Imp\IpImp;
use DucCnzj\Ip\Exceptions\IncorrectInstanceException;
use DucCnzj\Ip\Exceptions\IpProviderClassNotExistException;

trait HandleProvider
{
    /**
     * @var \Closure[]
     */
    protected $bindings = [];

    /**
     * @var array
     */
    protected $withConfigurations = ['taobao' => false];

    /**
     * @var array
     */
    protected $providerConfig = [];

    /**
     * @var array|null ['baidu', 'taobao']
     */
    protected $providers = [];

    /**
     * @param string $provider
     * @param string $concrete
     * @param bool $hasConfig
     *
     * @return HandleProvider
     *
     * @author duc <1025434218@qq.com>
     */
    public function bind(string $provider, $concrete, $hasConfig = true)
    {
        $this->bindings[$provider] = $this->getClosure($provider, $concrete);
        $this->withConfigurations = $this->withConfigurations + [$provider => $hasConfig];

        return $this;
    }

    /**
     * @param string $provider
     * @param string $concrete
     * @return \Closure
     *
     * @author duc <1025434218@qq.com>
     */
    protected function getClosure(string $provider, $concrete = null)
    {
        return function () use ($provider, $concrete) {
            return $this->createProvider($provider, $concrete);
        };
    }

    /**
     * @param string $provider
     *
     * @param string $concrete
     * @return IpImp
     * @throws IpProviderClassNotExistException
     * @throws \ReflectionException
     * @author duc <1025434218@qq.com>
     */
    protected function createProvider(string $provider, $concrete)
    {
        if (is_object($concrete)) {
            if (! $concrete instanceof IpImp) {
                $class = get_class($concrete);
                throw new IncorrectInstanceException("{$class} is not instanceof " . IpImp::class);
            }

            return $concrete;
        }

        $shortName = ucfirst(strtolower($provider)) . 'Ip';

        $class = $concrete ?: "\DucCnzj\Ip\Strategies\\{$shortName}";

        if (! class_exists($class)) {
            throw new IpProviderClassNotExistException("{$class} 不存在");
        }

        if (! (new \ReflectionClass($class))->implementsInterface(IpImp::class)) {
            throw new IncorrectInstanceException("{$class} is not instanceof " . IpImp::class);
        }

        $config = $this->getProviderConfig($provider);

        return new $class($config);
    }

    /**
     * @return array
     *
     * @throws IpProviderClassNotExistException
     * @author duc <1025434218@qq.com>
     */
    public function resolveProviders()
    {
        $providerClosures = [];

        foreach ($this->getProviders() as $provider) {
            if (! $this->shouldNotSkip($provider)) {
                continue;
            }

            if (! isset($this->bindings[$provider])) {
                $this->bindings[$provider] = $this->getClosure($provider);
            }

            $providerClosures[$provider] = $this->bindings[$provider];
        }

        return $providerClosures;
    }

    /**
     * @param string ...$provider
     *
     * @return HandleProvider
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
     * @param string ...$provider
     *
     * @return HandleProvider
     *
     * @author duc <1025434218@qq.com>
     */
    public function use(string ...$provider)
    {
        return $this->useProvider(...$provider);
    }

    /**
     * @return HandleProvider
     *
     * @author duc <1025434218@qq.com>
     */
    public function clearUse()
    {
        $this->providers = null;

        return $this;
    }

    /**
     * @param $provider
     * @return bool
     *
     * @author duc <1025434218@qq.com>
     */
    public function shouldNotSkip($provider): bool
    {
        if (isset($this->withConfigurations[$provider])) {
            if (! $this->withConfigurations[$provider] || ! ! $this->getProviderConfig($provider)) {
                return true;
            }
        }

        return ! ! $this->getProviderConfig($provider);
    }

    /**
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function getProviders(): array
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
     * @param string $name
     *
     * @return mixed|null
     *
     * @author duc <1025434218@qq.com>
     */
    public function getInstanceByName(string $name)
    {
        return isset($this->bindings[$name]) ? $this->bindings[$name] : null;
    }

    /**
     * @param string $provider
     *
     * @return array|string
     */
    public function getProviderConfig(string $provider)
    {
        if (! isset($this->providerConfig[$provider])) {
            return [];
        }

        return $this->providerConfig[$provider];
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
            'ali',
            'tencent',
            'taobao',
        ];
    }

    /**
     * @param string ...$names
     *
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function getConfigs(string ...$names): array
    {
        if (empty($names)) {
            return $this->providerConfig;
        }

        $result = [];
        foreach ($names as $provider) {
            $result[$provider] = $this->getProviderConfig($provider);
        }

        return $result;
    }

    /**
     * @param array $configs
     *
     * @return HandleProvider
     *
     * @author duc <1025434218@qq.com>
     */
    public function setConfigs(array $configs)
    {
        foreach ($configs as $provider => $config) {
            $this->setProviderConfig($provider, $config);
        }

        return $this;
    }

    /**
     * @param string $provider
     * @param array|string $config
     *
     * @return HandleProvider
     *
     * @author duc <1025434218@qq.com>
     */
    public function setProviderConfig(string $provider, $config)
    {
        $this->providerConfig[$provider] = $config;

        return $this;
    }
}

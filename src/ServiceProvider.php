<?php

namespace DucCnzj\Ip;

/**
 * Class ServiceProvider
 * @package DucCnzj\Ip
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     *
     * @author duc <1025434218@qq.com>
     */
    public function register()
    {
        $this->app->singleton(IpClient::class, function () {
            return new IpClient;
        });

        $this->app->alias(IpClient::class, 'ip');
    }

    /**
     * @return array
     *
     * @author duc <1025434218@qq.com>
     */
    public function provides()
    {
        return [IpClient::class, 'ip'];
    }
}

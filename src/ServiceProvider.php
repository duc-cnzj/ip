<?php

namespace DucCnzj\Ip;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(IpClient::class, function () {
            return new IpClient;
        });

        $this->app->alias(IpClient::class, 'ip');
    }

    public function provides()
    {
        return [IpClient::class, 'ip'];
    }
}

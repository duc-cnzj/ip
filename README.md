# ip

A SDK for ip.

[![Build Status](https://travis-ci.com/DuC-cnZj/ip.svg?branch=master)](https://travis-ci.com/DuC-cnZj/ip)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/DuC-cnZj/ip/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/DuC-cnZj/ip/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/DuC-cnZj/ip/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/DuC-cnZj/ip/?branch=master)
![StyleCI build status](https://github.styleci.io/repos/156356126/shield)

## Support

- baidu api
- taobao api
- ali api

## Installing

```shell
$ composer require duc_cnzj/ip -vvv
```

## Usage

```php
$client = new IpClient();

$client->setIp('xxx.xxx.xxx.xxx')->getCity();
$client->setIp('xxx.xxx.xxx.xxx')->getCountry();
$client->setIp('xxx.xxx.xxx.xxx')->getRegion();
$client->setIp('xxx.xxx.xxx.xxx')->getIp();

# or
$client->setIp('xxx.xxx.xxx.xxx');
$client->getCity();
$client->city;

# use Provider (baidu, taobao, ali)
$client = new IpClient();

# if use baidu api, you need provide ak secret.
$client->useProvider('baidu')
    ->setProviderConfig('baidu', ['ak' => 'xxxxxxxxxxxx']);

# if use ali api, you need provide ak secret.
$client->useProvider('ali')
    ->setProviderConfig('ali', ['app_code' => 'xxxxxxxxxxxx']);

# if use taobao.
$client->useProvider('taobao');

# default use both baidu and taobao. if you want use both, pls set baidu ak secret.
$client->setProviderConfig('baidu', ['ak' => 'xxxxxxxxxxxx']);

# package will try 3 times when provider response error. use try method to reset tryTimes.
$client->setIp('xxx.xxx.xxx.xxx')->try(10);

# in laravel
 $client = app('ip');
```

extra property
```php
# baidu/ali return point_x and point_y, so you can:
$client->useProvider('baidu')
    ->setProviderConfig('baidu', ['ak' => 'xxxxxxxxxxxx']);

$client->useProvider('ali')
    ->setProviderConfig('ali', ['app_code' => 'xxxxxxxxxxxx']);

$client->getPointX();
$client->getPointY();

# taobao return isp, so u can:

$client->useProvider('taobao')
    ->setIp('xxx.xxx.xxx.xxx')
    ->getIsp();
```

when getXXX() false it will return null, u can use `$client->getErrors()` get error info;

alias
```php
$client->use('taobao', 'baidu');
# Equals to
$client->useProvider('taobao', 'baidu');
```

other methods
```php
$client->cliearUse(); # will clear providers;

# custom your instance. need implement DucCnzj\Ip\Imp\IpImp;
$client->bound('taobao', $taobao);

# set custom CacheStore. default is ArrayStore. need implement DucCnzj\Ip\Imp\CacheStoreImp;
$client->setCacheStore($store);
```

## License

MIT

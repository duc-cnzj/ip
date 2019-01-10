# ip

A SDK for ip.

## Installing

```shell
$ composer require duccnzj/ip -vvv
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

# use Provider (baidu, taobao)
$client = new IpClient();

# if use baidu api, you need provide ak secret.
$client->useProvider('baidu')
    ->setProviderConfig('baidu', ['ak' => 'xxxxxxxxxxxx']);

# if use taobao.
$client->useProvider('taobao');

# default use both baidu and taobao. if you want use both, pls set baidu ak secret.
$client->setProviderConfig('baidu', ['ak' => 'xxxxxxxxxxxx']);

# package will try 3 times when provider response error. use try to reset tryTimes.
$client->setIp('xxx.xxx.xxx.xxx')->try(10);

# in laravel
 $client = app('ip');
```

extra property
```php
# baidu return point_x and point_y, so you can:
$client->useProvider('baidu')
    ->setProviderConfig('baidu', ['ak' => 'xxxxxxxxxxxx']);

$client->getPointX();
$client->getPointY();

# taobao return isp, so u can:

$client->useProvider('taobao')
    ->setIp('xxx.xxx.xxx.xxx')
    ->getIsp();
```

when getXXX() false it will return null, u can use `$client->getErrors()` get error info;


## License

MIT

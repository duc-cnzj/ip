# ip

A SDK for ip.

## Installing

```shell
$ composer require duccnzj/ip -vvv
```

## Usage

```php
(new IpClient('xxx.xxx.xxx.xxx'))->getIpInfo();

(new IpClient)->setIpAddress('xxx.xxx.xxx.xxx')->getIpInfo();
```

## License

MIT
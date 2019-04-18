<?php

namespace DucCnzj\Ip;

use GuzzleHttp\Client;
use DucCnzj\Ip\Imp\IpImp;
use GuzzleHttp\ClientInterface;
use DucCnzj\Ip\Traits\CacheResponse;
use DucCnzj\Ip\Imp\RequestHandlerImp;
use DucCnzj\Ip\Exceptions\ServerErrorException;
use DucCnzj\Ip\Exceptions\BreakLoopExceptionImp;
use DucCnzj\Ip\Exceptions\CantResolveClassException;

/**
 * Class RequestHandler
 * @package DucCnzj\Ip
 */
class RequestHandler implements RequestHandlerImp
{
    use CacheResponse;

    /**
     * @var ClientInterface|null
     */
    protected $client;

    /**
     * @var array
     */
    protected $errorStacks = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var int
     */
    protected $tryTimes = 3;

    /**
     * @return ClientInterface
     *
     * @author duc <1025434218@qq.com>
     */
    public function getClient(): ClientInterface
    {
        return is_null($this->client)
            ? $this->client = new Client()
            : $this->client;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $providers
     * @param string $ip
     *
     * @return array
     * @throws ServerErrorException
     * @author duc <1025434218@qq.com>
     */
    public function send(array $providers, string $ip)
    {
        if (empty($providers)) {
            return ['success'  => 0];
        }

        foreach ($providers as $name => $providerClosure) {
            if ($info = $this->getCacheStore()->get($this->cacheKey($name, $ip))) {
                return $info;
            }

            /** @var IpImp $provider */
            try {
                $provider = $providerClosure();
            } catch (CantResolveClassException $e) {
                $this->errorStacks[] = $e->getMessage();
                continue;
            }

            for ($time = 1; $time <= $this->getTryTimes(); $time++) {
                try {
                    $result = array_merge($provider->send($this->getClient(), $ip), [
                        'provider' => $name,
                        'success'  => 1,
                    ]);

                    $this->getCacheStore()->put($this->cacheKey($name, $ip), $result);

                    return $result;
                } catch (ServerErrorException $e) {
                    $this->logError($name, $e);

                    continue;
                } catch (BreakLoopExceptionImp $e) {
                    $this->logError($name, $e);

                    continue 2;
                } catch (\Exception $e) {
                    $this->logError($name, $e);

                    break 2;
                }
            }
        }

        if (! empty($this->errorStacks)) {
            throw new \RuntimeException(json_encode($this->errorStacks));
        }

        throw new ServerErrorException();
    }

    /**
     * @return int
     */
    public function getTryTimes(): int
    {
        return $this->tryTimes;
    }

    /**
     * @param int $tryTimes
     *
     * @return $this
     *
     * @author duc <1025434218@qq.com>
     */
    public function setTryTimes(int $tryTimes)
    {
        $this->tryTimes = $tryTimes;

        return $this;
    }

    /**
     * @param string     $name
     * @param \Exception $e
     *
     * @author duc <1025434218@qq.com>
     */
    public function logError(string $name, \Exception $e)
    {
        $this->errors[] = "provider: {$name}. " . $e->getMessage();
    }
}

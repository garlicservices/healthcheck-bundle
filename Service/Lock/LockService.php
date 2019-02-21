<?php

namespace Garlic\HealthCheck\Service\Lock;

use Symfony\Component\Cache\Traits\RedisTrait;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\RedisStore;

class LockService
{
    use RedisTrait;

    /** @var RedisStore $store */
    protected $store;
    /** @var Factory $factory */
    protected $factory;
    
    public function __construct(
        $host,
        $port
    ) {
        $dsn = 'redis://' . $host . ':' . $port;
        $redis = self::createConnection($dsn);
        $this->store = new RedisStore($redis);

        $this->factory = new Factory($this->store);
    }

    /**
     * @return RedisStore
     */
    public function getLockStorage()
    {
        return $this->store;
    }

    /**
     * @return Factory
     */
    public function getLockFactory()
    {
        return $this->factory;
    }
}

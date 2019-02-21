<?php

namespace Garlic\HealthCheck\Service\Lock;

use Predis\Client;
use Symfony\Component\Cache\Traits\RedisTrait;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\PdoStore;
use Symfony\Component\Lock\Store\RedisStore;

class LockService
{
    /** @var PdoStore $store */
    protected $store;
    /** @var Factory $factory */
    protected $factory;

    use RedisTrait;
    
    public function __construct(
        $host,
        $port,
        $dbname,
        $user,
        $password
    ) {
        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $dbname;
        $this->store = new PdoStore($dsn, ['db_username' => $user, 'db_password' => $password]);

        $this->factory = new Factory($this->store);

// should be done manually via migrations on the prod
//        $this->checkLockTableExists();
    }

    /**
     * @return PdoStore
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

    /**
     * @throws \Exception
     */
    protected function checkLockTableExists()
    {
        try {
            $this->store->createTable();
        } catch (\PDOException $exception) {
            throw new \Exception('Failed to instantiate lock table.');
        }
    }
}

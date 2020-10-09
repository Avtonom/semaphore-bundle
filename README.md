Avtonom semaphore bundle
===========================

Integrates zerkalica/semaphore library into Symfony2.

A generic locking Symfony bundle for PHP, that uses named locks for semaphore acquire and release, to guarantee atomicity.

Locking is useful for controlling access to resources in a multi-process or distributed environment.

The idea is that the actual locking mechanism can be implemented in any way you like, in order to fit your technology stack.

Page bundle: https://github.com/Avtonom/semaphore-bundle

##  Support adapters
* redis
* memcached
* sem (native functionality)
* apc
* flock (filesystem)
* - doctrine/orm , pdo, sql - (in future versions)

## Features

* Check for attempts to lock the same process. There are two variants of this reaction: throw an exception or extend the lifetime of the key.
* Check before removing the key, he put this process. It is to be found that the key did not die at the time and did not put another process.
* Notification that the script finished and forgot to remove the lock.
* Logging operations with all the necessary information on a specific channel in monolog (channel: semaphore)
* Select the class for the lock manager (SemaphoreManager)
* Select the object class contains a list of key locks (KeyStorage)
* Demo mode - keeping all logging but does not execute set and validates the key.
* Setting parameters such as:
* - Number of attempts to acquire a lock
* - Waiting time between attempts to acquire a lock
* - Lifetime lock
* - Prefix key lock

Maybe in the future:
* Notice that the lock had died on key lifetimes
* Adapters for doctrine/orm, pdo, sql
* The administrative interface (Sonata Project) for a list of locks and log management actions
* Stopwatch to see semaphore usage in the timeline of the symfony debug toolbar


#### To Install

Run the following in your project root, assuming you have composer set up for your project

```sh

composer.phar require avtonom/semaphore-bundle ^1.4
composer require snc/redis-bundle
```

Switching `^1.4` for the most recent tag.

Add the bundle to app/AppKernel.php

```php

$bundles(
    ...
       new Avtonom\SemaphoreBundle\AvtonomSemaphoreBundle(),
       new Snc\RedisBundle\SncRedisBundle(),
    ...
);

```

Configuration options (config.yaml):

``` yaml

snc_redis:
    clients:
        semaphore:
            type: predis
            alias: semaphore
            dsn: redis://localhost/2

avtonom_semaphore:
    adapter_redis_client: snc_redis.semaphore
    key_storage_class: Application\Avtonom\SemaphoreBundle\SemaphoreKeyStorage
    #mode: demo # activation of the demo mode - keeping all logging but does not execute set and validates the key. 
    # default
    #    adapter: avtonom_semaphore.adapter.redis
    #    manager_class: Avtonom\SemaphoreBundle\Model\SemaphoreManager
    #    is_exception_repeat_block_key: true # Generate an error when you try to re-key block in the same process.
    #    use_extended_methods: true

```

Configuration options (parameters.yaml):

``` yaml

parameters:
    # default
    #    avtonom_semaphore.try_count: 240 # try count, if lock not acquired. 240 count * 1/2 sec (sleep wait) = 120 sec wait
    #    avtonom_semaphore.sleep_time: 500000 # sleep time in microseconds, if lock not acquired. 1.000.000 microseconds = 1 seconds
    #    avtonom_semaphore.max_lock_time: 60 # ttl - max lock time
    #    avtonom_semaphore.prefix: 'lock_'
    
```

``` php

use Avtonom\SemaphoreBundle\Traits\SemaphoreTrait\SemaphoreTrait;

// or

/** @var $semaphore \Avtonom\Semaphore\Model\SemaphoreManagerInterface */
$semaphore = $container->get('avtonom_semaphore.manager');

$lockKeyStorage = $this->getLockKeyStorage();
$lockKey = [$lockKeyStorage::MY_KEY, $param1, $param2, $paramN];
$this->lockAcquire($lockKey, __METHOD__, 10 /* 10 - if you personal lock expire time in seconds. default 60 */);

// Do something thread-safe

$this->lockRelease($lockKey, __METHOD__);

```

Create Application\Avtonom\SemaphoreBundle\KeyStorage class:

``` php

<?php

namespace Application\Avtonom\SemaphoreBundle;

use Avtonom\SemaphoreBundle\Model\SemaphoreKeyStorageInterface;

class SemaphoreKeyStorage implements SemaphoreKeyStorageInterface
{
    const
        MY_KEY = 'M_K_',
        MY_OTHER_KEY = 'M_O_K_'
    ;
}

```

### Need Help?

1. Please look the log file %kernel.logs_dir%/%kernel.environment%.semaphore.log
2. Create an issue if you've found a bug,
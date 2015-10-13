<?php

namespace Avtonom\SemaphoreBundle\Traits;

use Avtonom\SemaphoreBundle\Model\SemaphoreKeyStorageInterface;
use Avtonom\SemaphoreBundle\Model\SemaphoreManagerInterface;

trait SemaphoreTrait
{
    /**
     * @var SemaphoreManagerInterface
     */
    protected $semaphore;

    /**
     * Acquire semaphore and return handle
     *
     * @param array $key
     * @param string|null $path
     * @param integer $ttl time to leave in seconds
     *
     * @throws \ErrorException
     */
    protected function lockAcquire($key, $path = null, $ttl = 60)
    {
        $this->semaphore->acquire($key, $path, $ttl);
    }

    /**
     * Release semaphore
     *
     * @param array $key
     * @param string|null $path
     *
     * @return void
     *
     * @throws \LogicException
     */
    protected function lockRelease($key, $path = null)
    {
        $this->semaphore->release($key, $path);
    }

    /**
     * @return SemaphoreKeyStorageInterface
     */
    protected function getLockKeyStorage()
    {
        return $this->semaphore->getKeyStorage();
    }

    /**
     * @return SemaphoreManagerInterface
     */
    public function getSemaphore()
    {
        return $this->semaphore;
    }

    /**
     * @param SemaphoreManagerInterface $semaphore
     */
    public function setSemaphore($semaphore)
    {
        $this->semaphore = $semaphore;
    }
}
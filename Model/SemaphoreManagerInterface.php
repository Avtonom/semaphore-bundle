<?php

namespace Avtonom\SemaphoreBundle\Model;

use Avtonom\SemaphoreBundle\Exception\SemaphoreAcquireException;
use Avtonom\SemaphoreBundle\Exception\SemaphoreReleaseException;

/**
 * Semaphore manager extender
 */
interface SemaphoreManagerInterface extends \Millwright\Semaphore\Model\SemaphoreManagerInterface
{
    /**
     * Acquire semaphore and return handle
     *
     * @param string $srcKey
     * @param string|null $path
     * @param integer|null $ttl time to leave in seconds
     *
     * @return mixed handle
     *
     * @throws SemaphoreAcquireException
     */
    public function acquire($srcKey, $path = null, $ttl = null);

    /**
     * Release semaphore
     *
     * @param array $srcKey
     * @param string|null $path
     *
     * @return void
     *
     * @throws SemaphoreReleaseException
     */
    public function release($srcKey, $path = null);

    /**
     * @return SemaphoreKeyStorageInterface
     */
    public function getKeyStorage();
}
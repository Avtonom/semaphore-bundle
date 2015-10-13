<?php

namespace Avtonom\SemaphoreBundle\Model;

/**
 * Semaphore adapter interface
 */
interface AdapterInterface extends \Millwright\Semaphore\Model\AdapterInterface
{
    /**
     * Acquire semaphore and return handle
     *
     * @param string  $key
     * @param string  $val
     * @param integer $ttl time to leave in seconds
     *
     * @return bool
     */
    function acquireEx($key, $val, $ttl);

    /**
     * Release semaphore
     *
     * @param string $handle handle from acquire
     * @param string $val
     *
     * @return bool
     *
     * @throws \UnexpectedValueException
     */
    function releaseEx($handle, $val);

    /**
     * @param string $key
     * @param string $ttl
     *
     * @return integer
     */
    function expire($key, $ttl);
}

<?php

namespace Avtonom\SemaphoreBundle\Adapter;

use Avtonom\SemaphoreBundle\Model\AdapterInterface;

/**
 * Redis semaphore adapter
 */
class RedisAdapter extends \Millwright\Semaphore\Adapter\RedisAdapter implements AdapterInterface
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
    public function acquireEx($key, $val, $ttl)
    {
        $acquired = $this->redis->setnx($key, $val);
        if ($acquired) {
            $this->redis->expire($key, $ttl);
            return true;
        }
        return false;
    }

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
    public function releaseEx($handle, $val)
    {
        if(!empty($val)){
            $keyVal = $this->redis->get($handle);
            if(!is_string($keyVal) || empty($keyVal) || $keyVal != $val){
                throw new \UnexpectedValueException('Not my key: '.json_encode([$val, $keyVal], JSON_UNESCAPED_UNICODE));
            }
        }
        return ($this->redis->del($handle) > 0);
    }

    /**
     * @param string $key
     * @param string $ttl
     *
     * @return integer
     */
    public function expire($key, $ttl)
    {
        return $this->redis->expire($key, $ttl);
    }
}
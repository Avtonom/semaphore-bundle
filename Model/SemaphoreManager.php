<?php

namespace Avtonom\SemaphoreBundle\Model;

use Avtonom\SemaphoreBundle\Exception\SemaphoreAcquireException;
use Avtonom\SemaphoreBundle\Exception\SemaphoreReleaseException;

/**
 * Semaphore manager extender
 *
 * @todo How view info about key dead by time
 */
class SemaphoreManager extends SemaphoreManagerBase implements SemaphoreManagerInterface
{
    /**
     * Acquire semaphore and return handle
     *
     * @param string  $srcKey
     * @param string|null $path
     * @param integer|null $ttl time to leave in seconds
     *
     * @return mixed handle
     *
     * @throws SemaphoreAcquireException
     */
    public function acquire($srcKey, $path = null, $ttl = null)
    {
        $key = null;
        $result = null;
        try {
            $key = $this->getKey($srcKey);
            if(array_key_exists($key, $this->handlers)){
                if($this->isExceptionRepeatBlockKey){
                    $this->logError('Попытка повторного блокирования ключа', $path, __FUNCTION__, $srcKey);
                    $this->acquireException('Попытка повторного блокирования ключа', $srcKey);
                } else {
                    $this->logger->warning(sprintf('[%d] %s->%s: Попытка повторного блокирования ключа: %s', getmypid(), $path, __FUNCTION__, $this->getDataToString($srcKey)));
                    $this->adapter->expire($key, $ttl);
                }
            }
            $this->logDebug('Start', $path, __FUNCTION__, $srcKey);
            $result = $this->parentAcquire($srcKey, $ttl);

        } catch(SemaphoreAcquireException $e){
            $this->acquireException($e->getMessage(), $srcKey);

        } catch(\ErrorException $e){
            $this->logException($e, $path, __FUNCTION__, $srcKey);
            $this->acquireException('Истекло время ожидания блокировки ключа', $srcKey);

        } catch(\Exception $e){
            $this->logException($e, $path, __FUNCTION__, $srcKey);
            $this->acquireException('Ошибка', $srcKey);
        }
        $this->logDebug(sprintf('Success (ttl %d s.)', $ttl), $path, __FUNCTION__, $srcKey);
        return $result;
    }

    /**
     * @param $srcKey
     * @param integer|null $maxLockTime
     *
     * @return string
     *
     * @throws \ErrorException
     */
    protected function parentAcquire($srcKey, $maxLockTime = null)
    {
        $key = $this->getKey($srcKey);
        $tryCount = $this->tryCount;
        $maxLockTime = (is_numeric($maxLockTime) && $maxLockTime) ? $maxLockTime : $this->maxLockTime;
        $ok      = null;

        while ($tryCount > 0 && !$ok = $this->acquireFactory($key, $maxLockTime)) {
            $tryCount--;
            sleep($this->sleepTime);
        }

        if (!$ok) {
            throw new \ErrorException(sprintf('Can\'t acquire lock for %s', $key));
        } else {
            $this->handlers[$key] = $this->getDataToString($srcKey);
        }

        return $key;
    }

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
    public function release($srcKey, $path = null)
    {
        try {
            $this->logDebug('Start', $path, __FUNCTION__, $srcKey);

            $res = $this->parentRelease($srcKey);
            if($res === false){
                $this->logError('Освобождение несуществующего ключа', $path, __FUNCTION__, $srcKey);
                $this->releaseException('Освобождение несуществующего ключа', $srcKey);
            }
        } catch(\LogicException $e) {
            $this->logException($e, $path, __FUNCTION__, $srcKey);
            $this->releaseException('Повторная попытка освобождения ключа', $srcKey);

        } catch(\UnexpectedValueException $e) {
            $this->logException($e, $path, __FUNCTION__, $srcKey);
            $this->releaseException('Освобождение несуществующего ключа', $srcKey);

        } catch(\Exception $e){
            $this->logException($e, $path, __FUNCTION__, $srcKey);
            $this->releaseException('Ошибка', $srcKey);
        }
        $this->logDebug('Success', $path, __FUNCTION__, $srcKey);
    }

    /**
     * @param $srcKey
     *
     * @return bool
     *
     * @throws \LogicException
     */
    protected function parentRelease($srcKey)
    {
        $key = $this->getKey($srcKey);
        if (!array_key_exists($key, $this->handlers)) {
            throw new \LogicException(sprintf('Call ::acquire(\'%s\') first', $key));
        }
        unset($this->handlers[$key]);
        return $this->adapter->releaseEx($key, getmypid());
    }

    /**
     * @return SemaphoreKeyStorageInterface
     */
    public function getKeyStorage()
    {
        return $this->keyStorage;
    }

    public function __destruct()
    {
        if(!empty($this->handlers)){
            $this->logError('Handlers is not empty', null, __FUNCTION__, $this->handlers);
        }
    }
}

<?php

namespace Avtonom\SemaphoreBundle\Model;

use Avtonom\SemaphoreBundle\Exception\SemaphoreAcquireException;
use Avtonom\SemaphoreBundle\Exception\SemaphoreReleaseException;
use Avtonom\SemaphoreBundle\Exception\SemaphoreException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Psr\Log\LoggerInterface;
use Millwright\Semaphore\Model\AdapterInterface;

abstract class SemaphoreManagerBase
{
    /**
     * @var SemaphoreKeyStorageInterface
     */
    protected $keyStorage;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    protected $tryCount;
    protected $sleepTime;
    protected $maxLockTime;
    protected $isExceptionRepeatBlockKey;
    protected $useExtendedMethods;
    protected $prefix;

    protected $handlers = array();

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param AdapterInterface $adapter
     * @param SemaphoreKeyStorageInterface $keyStorage
     * @param integer          $tryCount  try count, if lock not acquired
     * @param integer          $sleepTime time in seconds , if lock not acquired wait and try again
     * @param integer          $maxLockTime
     * @param bool             $isExceptionRepeatBlockKey
     * @param bool             $useExtendedMethods
     * @param string           $prefix    lock key namespace
     * @param LoggerInterface  $logger
     *
     * @throws SemaphoreException
     */
    public function __construct(AdapterInterface $adapter, SemaphoreKeyStorageInterface $keyStorage, $tryCount, $sleepTime, $maxLockTime, $isExceptionRepeatBlockKey, $useExtendedMethods, $prefix, LoggerInterface $logger)
    {
        $this->adapter               = $adapter;
        $this->keyStorage            = $keyStorage;
        $this->tryCount              = $tryCount;
        $this->sleepTime             = $sleepTime;
        $this->maxLockTime           = $maxLockTime;
        $this->isExceptionRepeatBlockKey    = $isExceptionRepeatBlockKey;
        $this->useExtendedMethods    = $useExtendedMethods;
        $this->prefix                = $prefix;
        $this->logger                = $logger;

        if($this->useExtendedMethods && !$adapter instanceof \Avtonom\SemaphoreBundle\Model\AdapterInterface){
            throw new SemaphoreException('ss');
        }
    }

    /**
     * @param string $key
     * @param integer $maxLockTime
     *
     * @return bool
     */
    protected function acquireFactory($key, $maxLockTime)
    {
        return ($this->useExtendedMethods) ? $this->adapter->acquireEx($key, getmypid(), $maxLockTime) : $this->adapter->acquire($key, $maxLockTime);
    }

    /**
     * @param string $key
     *
     * @return bool|void
     */
    protected function releaseFactory($key)
    {
        return ($this->useExtendedMethods) ? $this->adapter->releaseEx($key, getmypid()) : $this->adapter->release($key);
    }

    /**
     * @param array|object|string $srcKey
     *
     * @return string
     */
    protected function getKey($srcKey)
    {
        if(is_array($srcKey)){
            $key = $srcKey[0].md5(json_encode($srcKey));

        } elseif (is_object($srcKey)) {
            $key = ($srcKey instanceof ObjectIdentityInterface) ? (string) $srcKey : (string) ObjectIdentity::fromDomainObject($srcKey);

        } else {
            $key = $srcKey;
        }
        return $this->prefix . $key;
    }

    /**
     * @param string $message
     * @param mixed $srcKey
     *
     * @throws SemaphoreReleaseException
     */
    protected function releaseException($message, $srcKey)
    {
        throw new SemaphoreReleaseException(sprintf('%s: (%s) %s', $message, $this->getKey($srcKey), json_encode($srcKey, JSON_UNESCAPED_UNICODE)));
    }

    /**
     * @param string $message
     * @param mixed $srcKey
     *
     * @throws SemaphoreAcquireException
     */
    protected function acquireException($message, $srcKey)
    {
        throw new SemaphoreAcquireException(sprintf('%s: (%s) %s', $message, $this->getKey($srcKey), json_encode($srcKey, JSON_UNESCAPED_UNICODE)));
    }

    /**
     * @param string $message
     * @param string $path
     * @param string $functionName
     * @param mixed $srcKey
     */
    protected function logDebug($message, $path, $functionName, $srcKey)
    {
        $this->logger->debug(sprintf('[%d] %s->%s: %s. Key: %s %s', getmypid(), $path, $functionName, $message, $this->getKey($srcKey), $this->getDataToString($srcKey)));

    }

    /**
     * @param string $message
     * @param string $path
     * @param string $functionName
     * @param mixed $srcKey
     */
    protected function logError($message, $path, $functionName, $srcKey)
    {
        $this->logger->error(sprintf('[%d] %s->%s: %s. Key: %s %s', getmypid(), $path, $functionName, $message, $this->getKey($srcKey), $this->getDataToString($srcKey)));
    }

    /**
     * @param \Exception $e
     * @param string $path
     * @param string $functionName
     * @param mixed $srcKey
     */
    protected function logException(\Exception $e, $path, $functionName, $srcKey)
    {
        $this->logger->error(sprintf('[%d] %s->%s: %s: %s. Key: %s %s', getmypid(), $path, $functionName, get_class($e), $e->getMessage(), $this->getKey($srcKey), $this->getDataToString($srcKey)));
    }

    /**
     * @param string|object $srcKey
     * @return string
     */
    protected function getDataToString($srcKey)
    {
        if (is_object($srcKey)) {
            $key = ($srcKey instanceof ObjectIdentityInterface) ? (string) $srcKey : (string) ObjectIdentity::fromDomainObject($srcKey);
        } else {
            $key = $srcKey;
        }
        return json_encode($key, JSON_UNESCAPED_UNICODE);
    }
}
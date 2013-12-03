<?php

namespace Riland\GearmanBundle\Gearman\Config;

use Riland\GearmanBundle\Exception\WorkerConfigUndefinedParameterException;

class WorkerConfig
{
    const DEFAULT_LIFETIME = 9600;
    const DEFAULT_TIMEOUT = 60;

    /**
     * @var string
     */
    private $worker;

    /**
     * @var integer
     */
    private $lifetime;

    /**
     * @var integer
     */
    private $timeout;

    /**
     * @param array $parameters
     * @throws \Riland\GearmanBundle\Exception\WorkerConfigUndefinedParameterException
     */
    public function __construct(array $parameters = array())
    {
        foreach ($parameters as $key => $value) {
            if ('alias' == $key) {
                continue;
            }
            if (!property_exists($this, $key)) {
                throw new WorkerConfigUndefinedParameterException($key);
            }
            call_user_func_array(array($this, 'set'.ucfirst($key)), array($value));
        }
    }

    /**
     * @param string $worker
     * @return $this
     */
    public function setWorker($worker)
    {
        $this->worker = $worker;

        return $this;
    }

    /**
     * @return string
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * @param int $lifetime
     * @return $this
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * @return int
     */
    public function getLifetime()
    {
        if (is_null($this->lifetime)) {
            return self::DEFAULT_LIFETIME;
        }

        return $this->lifetime;
    }

    /**
     * @param int $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        if (is_null($this->timeout)) {
            return self::DEFAULT_TIMEOUT;
        }

        return $this->timeout;
    }

}
<?php

namespace Riland\GearmanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Riland\AirBundle\Entity\Timestable\TimestableInterface;
use Riland\AirBundle\Entity\Timestable\TimestableTrait;
use Riland\GearmanBundle\Exception\TaskStatusException;

/**
 * Task
 *
 * @ORM\Table("task")
 * @ORM\Entity(repositoryClass="Riland\GearmanBundle\Entity\Repository\TaskRepository")
 */
class Task implements TimestableInterface
{

    use TimestableTrait;

    const STATUS_NEW = 'new';
    const STATUS_PROCESSING = 'processing';
    const STATUS_READY = 'ready';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=10)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="priority", type="integer")
     */
    private $priority;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="parent", type="integer", nullable = true)
     */
    private $parent;

    /**
     * @var string
     *
     * @ORM\Column(name="parameters", type="json_array", nullable = true)
     */
    private $parameters;

    /**
     * @var string
     *
     * @ORM\Column(name="result", type="json_array", nullable = true)
     */
    private $result;

    /**
     * @var int
     *
     * @ORM\Column(name="lifetime", type="integer")
     */
    private $lifetime;

    /**
     * @var integer
     *
     * @ORM\Column(name="timeout", type="integer")
     */
    private $timeout;

    function __construct()
    {
        $this->status = self::STATUS_NEW;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $parent
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return int
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $parameters
     * @return $this
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return string
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function addParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getParameter($name)
    {
        return $this->parameters[$name];
    }

    /**
     * @param integer $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $result
     * @return $this
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $status
     * @return $this
     * @throws \Riland\GearmanBundle\Exception\TaskStatusException
     */
    public function setStatus($status)
    {
        if (!in_array($status, self::getAllowStatuses())) {
            throw new TaskStatusException($status);
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param integer $lifetime
     * @return $this
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * @return integer
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * @param integer $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @return integer
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @return array
     */
    public static function getAllowStatuses()
    {
        return array(
            self::STATUS_NEW,
            self::STATUS_PROCESSING,
            self::STATUS_READY
        );
    }

    /**
     * @return bool
     */
    public function isDone()
    {
        return self::STATUS_READY == $this->getStatus();
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        $currentDate = new \DateTime();

        return $currentDate->getTimestamp() - $this->getUpdated()->getTimestamp() > $this->getLifetime();
    }

}


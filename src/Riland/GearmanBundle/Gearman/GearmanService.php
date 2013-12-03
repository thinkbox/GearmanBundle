<?php

namespace Riland\GearmanBundle\Gearman;

use Doctrine\ORM\EntityManager;
use Mmoreram\GearmanBundle\Service\GearmanClient;
use Riland\GearmanBundle\Entity\Task;
use Riland\GearmanBundle\Exception\RedefineWorkerException;
use Riland\GearmanBundle\Gearman\Config\WorkerConfig;
use Riland\GearmanBundle\Worker\AbstractWorker;

class GearmanService
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Mmoreram\GearmanBundle\Service\GearmanClient
     */
    public $gearman;

    /**
     * @var array
     */
    private $workers = array();

    /**
     * @var array
     */
    private $workersConfig = array();

    /**
     * @param EntityManager $em
     * @param GearmanClient $gearman
     */
    public function __construct(EntityManager $em, GearmanClient $gearman)
    {
        $this->em = $em;
        $this->gearman = $gearman;
    }

    /**
     * @param AbstractWorker $worker
     * @param array $parameters
     * @return $this
     * @throws \Riland\GearmanBundle\Exception\RedefineWorkerException
     */
    public function addWorker(AbstractWorker $worker, array $parameters = array())
    {
        $workerName = str_replace('\\', '', get_class($worker));
        $name = array_key_exists('alias', $parameters) ? $parameters['alias'] : $workerName;
        if (array_key_exists($name, $this->workers)) {
            throw new RedefineWorkerException($name);
        }

        $this->workers[$name] = $this->getWorker($workerName);
        $this->workersConfig[$name] = new WorkerConfig($parameters);

        return $this;
    }

    /**
     * Get task result or create new task
     *
     * @param string $type
     * @param array $parameters
     * @return mixed
     */
    public function getResult($type, array $parameters = array())
    {
        $task = $this->getTask($type, $parameters);
        if ($task->isDone()) {
            $result = $this->getWorker($task)->getResult($task->getResult());

            if (0 == $task->getLifetime()) {
                $this->em->remove($task);
                $this->em->flush();
            }

            return $result;
        }

        return false;
    }

    /**
     * @param string $type
     * @param array $parameters
     * @return Task
     */
    private function getTask($type, array $parameters = array())
    {
        /* @var $task Task */
        $task = $this->em->getRepository('RilandGearmanBundle:Task')->findOneBy(array('type' => $type, 'parameters' => json_encode($parameters)));

        if (is_null($task)) {
            return $this->createTask($type, $parameters);
        }

        if (0 != $task->getLifetime() && $task->isExpired()) {
            $this->em->remove($task);
            $this->em->flush();

            return $this->createTask($type, $parameters);
        }

        return $task;
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function getWorker($name)
    {
        return $this->gearman->getWorker($name);
    }

    /**
     * @param string $type
     * @param array $parameters
     * @param integer $parent
     * @param bool $unique
     * @return Task
     */
    public function createTask($type, array $parameters = array(), $parent = null, $unique = true)
    {
        $job = $this->getJob($type);

        $task = new Task();
        $task->setType($type);
        $task->setParent($parent);
        $task->setParameters($parameters);
        $task->setLifetime($this->getWorkerConfig($task)->getLifetime());
        $task->setTimeout($this->getWorkerConfig($task)->getTimeout());
        $task->setPriority(1);

        $this->em->persist($task);
        $this->em->flush();
        $this->gearman->doBackgroundJob($job, $unique ? $type.'~'.$task->getId() : $type);

        return $task;
    }

    private function getJob($name)
    {
        list($workerName, $jobName) = explode('.', $name);

        if (!array_key_exists($workerName, $this->workers)) {
            throw new \Exception('no worker');
        }

        $worker = $this->workers[$workerName];
        foreach ($worker['jobs'] as $job) {
            if ($jobName == $job['callableName']) {
                return $job['realCallableNameNoPrefix'];
            }
        }

        throw new \Exception('no job');
    }

    private function getWorkerConfig(Task $task)
    {
        list($workerName) = explode('.', $task->getType());

        return $this->workersConfig[$workerName];
    }
}
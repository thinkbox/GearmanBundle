<?php

namespace Riland\GearmanBundle\Worker;

use Doctrine\ORM\EntityManager;
use Riland\GearmanBundle\Entity\Task;
use Riland\GearmanBundle\Gearman\GearmanService;

abstract class AbstractWorker
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var GearmanService
     */
    private $gearman;

    /**
     * @param EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return EntityManager
     */
    final protected function getGearmanEM()
    {
        return $this->em;
    }

    /**
     * @param GearmanService $gearman
     */
    public function setGearman(GearmanService $gearman)
    {
        $this->gearman = $gearman;
    }

    /**
     * @return GearmanService
     */
    final protected function getGearman()
    {
        return $this->gearman;
    }

    /**
     * @param \GearmanJob $job
     * @return Task
     * @throws \Exception
     */
    public function getTask(\GearmanJob $job)
    {
//        $this->garbageCollection();
//        $this->timeoutCollection();
        list($type) = explode('~', $job->workload());
        $task = $this->em->getRepository('RilandGearmanBundle:Task')->get($type);

        if (is_null($task)) {
            throw new \Exception('no task');
        }

        $task->setStatus(Task::STATUS_PROCESSING);
        $this->em->persist($task);
        $this->em->flush();

        return $task;
    }


    public function completeTask(Task $task)
    {
        $task->setStatus(Task::STATUS_READY);

        $this->em->persist($task);
        $this->em->flush();
        echo sprintf('Task %s with id %s complete!', $task->getType(), $task->getId()).PHP_EOL;
    }

    /**
     * @param array $parameters
     * @return Task
     */
    public function findTask(array $parameters)
    {
        return $this->em->getRepository('RilandGearmanBundle:Task')->findOneBy($parameters);
    }

}



<?php

namespace Riland\GearmanBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Riland\GearmanBundle\Entity\Task;

class TaskRepository extends EntityRepository
{
    /**
     * @param string $type
     * @return Task
     */
    public function get($type)
    {
        $qb = $this->createQueryBuilder('e');

        $qb
            ->where('e.status = :status')
            ->andWhere('e.type = :type')
            ->setParameter('type', $type)
            ->orderBy('e.priority', 'DESC')
            ->setParameter('status', Task::STATUS_NEW)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}



<?php

namespace Riland\GearmanBundle\Exception;


use Riland\GearmanBundle\Entity\Task;

class TaskStatusException extends \Exception
{

    public function __construct($status)
    {
        parent::__construct(sprintf('Status %s is undefined. Allow statuses: %s', $status, implode(', ', Task::getAllowStatuses())));

    }
}
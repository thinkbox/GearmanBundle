<?php

namespace Riland\GearmanBundle\Exception;

class WorkerConfigUndefinedParameterException extends \Exception
{

    public function __construct($name)
    {
        parent::__construct(sprintf('Undefined worker config parameter %s', $name));

    }
}
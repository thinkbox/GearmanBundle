<?php

namespace Riland\GearmanBundle\Exception;


class RedefineWorkerException extends \Exception
{

    public function __construct($name)
    {
        parent::__construct(sprintf('Redefine worker %s', $name));

    }
}
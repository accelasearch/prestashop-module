<?php

namespace Accelasearch\Accelasearch\Cron;

class Cron
{
    private $operations = [];

    public function execute()
    {
        foreach ($this->operations as $operation) {
            if (!$operation->isOperationToExecute())
                continue;
            $operation->executeAsync();
        }
    }

    public function addOperation(Operation\OperationAbstract $operation)
    {
        $this->operations[] = $operation;
    }

}
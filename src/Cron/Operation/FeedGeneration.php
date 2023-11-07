<?php

namespace Accelasearch\Accelasearch\Cron\Operation;

class FeedGeneration extends OperationAbstract
{
    public function execute()
    {
        $this->updateExecutionTime();
        echo "Done.";
    }

    public function getUpdateTiming(): int
    {
        return 60;
    }
}
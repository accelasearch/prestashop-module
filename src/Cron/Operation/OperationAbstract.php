<?php

namespace Accelasearch\Accelasearch\Cron\Operation;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Cron\Cron;
use Accelasearch\Accelasearch\Entity\Lock;

abstract class OperationAbstract
{
    abstract public function execute();
    abstract public function getUpdateTiming();

    public function executeAsync()
    {
        \Shop::setContext(\Shop::CONTEXT_ALL);
        $url = Cron::getUrl($this->getClassName());
        @file_get_contents(
            $url,
            false,
            stream_context_create([
                "http" => ["timeout" => 1]
            ])
        );
        return true;
    }

    private function getClassName()
    {
        $class = get_class($this);
        $class = explode("\\", $class);
        $class = end($class);
        return $class;
    }
    public function getExecutionTime(): int
    {
        return (int) Config::get("_ACCELASEARCH_LAST_" . $this->getClassName() . "_UPDATE", time() - (3600 * 48));
    }

    public function updateExecutionTime(): bool
    {
        \Shop::setContext(\Shop::CONTEXT_ALL);
        return Config::updateValue("_ACCELASEARCH_LAST_" . $this->getClassName() . "_UPDATE", time());
    }

    public function lock()
    {
        (new Lock($this->getClassName()))->create();
    }

    public function unlock()
    {
        (new Lock($this->getClassName()))->delete();
    }

    public function isLocked(): bool
    {
        return (new Lock($this->getClassName()))->isLocked();
    }

    public function isOperationToExecute(): bool
    {
        $lastExecution = $this->getExecutionTime();
        $interval = $this->getUpdateTiming();
        $now = time();
        if ($now - $lastExecution > $interval) {
            return true;
        }
        return false;
    }
}

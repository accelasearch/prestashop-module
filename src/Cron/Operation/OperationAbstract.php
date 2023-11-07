<?php

namespace Accelasearch\Accelasearch\Cron\Operation;

use Accelasearch\Accelasearch\Config\Config;

abstract class OperationAbstract
{
    abstract public function execute();
    abstract public function getUpdateTiming();

    public function executeAsync()
    {
        \Shop::setContext(\Shop::CONTEXT_ALL);
        $url = \Context::getContext()->link->getModuleLink('accelasearch', 'cron', [
            "ajax" => 1,
            "operation" => $this->getClassName(),
            "token" => Config::get("_ACCELASEARCH_CRON_TOKEN")
        ]);
        dump($url);
        file_get_contents(
            $url,
            false,
            stream_context_create([
                "http" => ["timeout" => 1]
            ])
        );
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
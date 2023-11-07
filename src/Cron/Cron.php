<?php

namespace Accelasearch\Accelasearch\Cron;

use Accelasearch\Accelasearch\Config\Config;

class Cron
{
    private $operations = [];

    public function execute()
    {
        foreach ($this->operations as $operation) {
            if (!$operation->isOperationToExecute() || $operation->isLocked())
                continue;
            $operation->executeAsync();
        }
    }

    public function addOperation(Operation\OperationAbstract $operation)
    {
        $this->operations[] = $operation;
    }

    /**
     * Cron is ready if the module is configured and at least one shop is configured
     */
    public static function isReady(): bool
    {
        $shops = Config::getShopsToSync();
        return (int) Config::get("_ACCELASEARCH_ONBOARDING") > 2 && !empty($shops);
    }

    public static function getUrl($operation)
    {
        return _PS_BASE_URL_ . __PS_BASE_URI__ . "modules/accelasearch/cron.php?operation=" . $operation . "&token=" . Config::get("_ACCELASEARCH_CRON_TOKEN");
    }

}
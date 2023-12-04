<?php

namespace Accelasearch\Accelasearch\Cron;

use Accelasearch\Accelasearch\Config\Config;
use Tools;

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
        return Tools::getShopDomainSsl(true) . "/modules/accelasearch/cron.php?operation=" . $operation . "&token=" . Config::get("_ACCELASEARCH_CRON_TOKEN");
    }

    public function updateCronjobLastexec()
    {
        Config::updateValue("_ACCELASEARCH_CRONJOB_LASTEXEC", time());
    }

    public function updateOnboarding()
    {
        $onBoarding = (int) Config::get("_ACCELASEARCH_ONBOARDING");
        if ($onBoarding === 2) {
            Config::updateValue("_ACCELASEARCH_ONBOARDING", 3);
        }
    }

}
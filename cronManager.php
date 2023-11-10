<?php

require_once __DIR__ . '/../../config/config.inc.php';
require_once __DIR__ . '/vendor/autoload.php';

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Cron\Cron;
use Accelasearch\Accelasearch\Cron\Operation\FeedGeneration;

$token = Tools::getValue('token');
if ($token !== Config::get("_ACCELASEARCH_CRON_TOKEN")) {
    die("Invalid token");
}

$cron = new Cron();
$cron->addOperation(new FeedGeneration());
$cron->execute();

$cron->updateOnboarding();

$cron->updateCronjobLastexec();

echo "Cronjob actions scheduled\n";
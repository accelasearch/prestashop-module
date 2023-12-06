<?php
use Accelasearch\Accelasearch\Cron\Cron;

require_once __DIR__ . "/../../config/config.inc.php";
require_once __DIR__ . "/vendor/autoload.php";

@ignore_user_abort(true);
@set_time_limit(0);

$operation = Tools::getValue("operation");
$token = Tools::getValue("token");
if ($token != Configuration::get("_ACCELASEARCH_CRON_TOKEN")) {
    die("Invalid token");
}

$module = Module::getInstanceByName("accelasearch");

if (!Cron::isReady() && $module->active)
    die("Cron is not ready and not configured yet or module is not active");

$operation = "Accelasearch\\Accelasearch\\Cron\\Operation\\" . $operation;
if (!class_exists($operation))
    die("Invalid operation");
$operation = new $operation();
$operation->execute();
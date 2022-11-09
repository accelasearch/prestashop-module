<?php

include_once(dirname(__FILE__) . '/../../config/config.inc.php');
$token = $_GET["token"] ?? NULL;
$wait = $_GET["wait"] ?? true;
$origin = $_GET["origin"] ?? "cronjob";
if($token === Configuration::get("ACCELASEARCH_CRON_TOKEN")){
  $accelasearch = Module::getInstanceByName('accelasearch');
  if($accelasearch->active){
    if($origin !== "pageview") Configuration::updateGlobalValue("ACCELASEARCH_LAST_CRONJOB_EXECUTION", time());
    $accelasearch->hookActionCronJob($wait);
  }
}

?>

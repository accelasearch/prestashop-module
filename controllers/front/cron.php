<?php

class AccelasearchCronModuleFrontController extends ModuleFrontController
{
    public $ajax;
    public function initContent()
    {
        parent::initContent();
        $this->ajax = true;
    }

    public function postProcess()
    {
        @ignore_user_abort(true);
        @set_time_limit(0);
        $operation = Tools::getValue("operation");
        $token = Tools::getValue("token");
        if ($token != Configuration::get("_ACCELASEARCH_CRON_TOKEN")) {
            die("Invalid token");
        }
        $operation = "Accelasearch\\Accelasearch\\Cron\\Operation\\" . $operation;
        if (!class_exists($operation))
            die("Invalid operation");
        $operation = new $operation();
        return $operation->execute();
    }
}
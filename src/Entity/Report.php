<?php

namespace Accelasearch\Accelasearch\Entity;

use Accelasearch\Accelasearch\Api\DgcalClient;
use Accelasearch\Accelasearch\Config\Config;
use Db;

class Report
{
    private $title;
    private $scope;
    public const EXPIRED_LOCK = "EXPIRED_LOCK";

    public function __construct(string $title, string $scope)
    {
        $this->title = $title;
        $this->scope = $scope;
    }

    private function getAccelasearchConfigurationState()
    {
        $configurations = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "configuration WHERE name LIKE '_ACCELASEARCH_%'");
        $config = [];
        foreach ($configurations as $configuration) {
            $config[$configuration['name']] = $configuration['value'];
        }
        return $config;
    }

    public function getReportData()
    {
        $report = [];
        ob_start();
        phpinfo();
        $pinfo = ob_get_contents();
        ob_end_clean();
        $report["phpinfo"] = $pinfo;
        $report["lock"] = Lock::getExpiredLocks();
        $report["config"] = $this->getAccelasearchConfigurationState();
        return $report;
    }

    public function send()
    {
        $client = DgcalClient::getInstance();
        return $client->post(Config::DGCAL_ENDPOINT . "report", [], [
            "title" => $this->title,
            "scope" => $this->scope,
            "data" => json_encode($this->getReportData())
        ]);
    }
}
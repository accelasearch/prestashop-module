<?php

namespace Accelasearch\Accelasearch\Module;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Cron\Cron;
use Accelasearch\Accelasearch\Exception\ModuleUpdateException;
use ZipArchive;
use Accelasearch\Accelasearch\Api\DgcalClient;

class Downloader
{

    private function getLatestVersion()
    {
        $client = DgcalClient::getInstance();
        $data = $client->get(Config::DGCAL_ENDPOINT . "module/getLatestVersion");
        return $data["data"]["version"] ?? null;
    }

    private function getLatestZip()
    {
        $client = DgcalClient::getInstance();
        $data = $client->getLatestZip();
        return $data;
    }

    public function needUpdate($current_version)
    {
        if (!Cron::isReady())
            return false;
        $latest = $this->getLatestVersion();
        return version_compare($current_version, $latest, '<');
    }

    private function writeZip($zip, $module)
    {
        $path = realpath($module->getLocalPath() . "/../");
        file_put_contents($path . "/accelasearch.zip", $zip);
    }

    private function extractZip($module)
    {
        $path = realpath($module->getLocalPath() . "/../");
        $file = $path . "/accelasearch.zip";
        $zip = new ZipArchive();
        $res = $zip->open($file);
        if ($res === true) {
            $zip->extractTo($path);
            $zip->close();
        } else {
            throw new ModuleUpdateException("Unable to extract zip");
        }
    }

    private function deleteZip($module)
    {
        $path = realpath($module->getLocalPath() . "/../");
        $file = $path . "/accelasearch.zip";
        return unlink($file);
    }

    public function updateModule(\Module $module)
    {
        try {
            $latestZip = $this->getLatestZip();
            $this->writeZip($latestZip, $module);
            $this->extractZip($module);
            $this->deleteZip($module);
        } catch (\Exception $e) {
            throw new ModuleUpdateException("Unable to get latest version - " . $e->getMessage());
        }
    }

}
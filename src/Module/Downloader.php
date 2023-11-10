<?php

namespace Accelasearch\Accelasearch\Module;

use Accelasearch\Accelasearch\Exception\ModuleUpdateException;
use Ector\ReleaseDownloader\Downloader as ReleaseDownloader;

class Downloader
{
    const OWNER = "buggyzap";
    const REPO = "accelasearch";
    const TOKEN = null;
    const TEST_MODE = true;

    private $downloader;

    public function __construct()
    {
        //TODO: Uncomment this line to enable auto update
        // $this->downloader = new ReleaseDownloader(self::OWNER, self::REPO, null, self::TOKEN);
    }

    public function needUpdate($current_version)
    {
        if (self::TEST_MODE)
            return true;
        $latest = $this->downloader->getLatestTagName();
        return version_compare($current_version, $latest, '<');
    }

    public static function needUpdateStatic($current_version)
    {
        $instance = new self();
        return $instance->needUpdate($current_version);
    }

    private function download(\Module $module)
    {
        $dir = realpath($module->getLocalPath() . "../fakedownloaddir") . "/";
        if (self::TEST_MODE)
            dump("Download to " . $dir . $module->name . '.zip');
        else
            $this->downloader->download($dir . "/");
    }

    private function extract()
    {
        if (self::TEST_MODE)
            dump("Extract to destination");
        else
            $this->downloader->extract();
    }

    public function updateModule(\Module $module)
    {
        try {
            $this->downloader->addAssetToDownload($module->name . '.zip');
            $this->download($module);
            $this->extract();
        } catch (\Exception $e) {
            throw new ModuleUpdateException("Unable to get latest version");
        }
    }

}
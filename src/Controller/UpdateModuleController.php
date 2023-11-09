<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Exception\ModuleUpdateException;
use Accelasearch\Accelasearch\Module\Downloader;

class UpdateModuleController extends AbstractController implements ControllerInterface
{
    private $module;
    public function __construct(\Module $module)
    {
        $this->module = $module;
    }
    public function handleRequest()
    {
        $downloader = new Downloader();
        if (!$downloader->needUpdate($this->module->version))
            $this->error('No update available', 400);
        try {
            $downloader->updateModule($this->module);
        } catch (ModuleUpdateException $e) {
            $this->error($e->getMessage(), 500);
        }
        $this->success(true);
    }
}
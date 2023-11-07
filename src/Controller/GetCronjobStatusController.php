<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Config\Config;

class GetCronjobStatusController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        // get default language
        $executed = (int) Config::get("_ACCELASEARCH_CRONJOB_LASTEXEC", 0);
        $this->success([
            "executed" => $executed !== 0,
        ]);
    }
}
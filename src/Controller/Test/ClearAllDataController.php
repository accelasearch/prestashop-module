<?php

namespace Accelasearch\Accelasearch\Controller\Test;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Controller\AbstractController;
use Accelasearch\Accelasearch\Controller\ControllerInterface;

class ClearAllDataController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        Config::updateValue('_ACCELASEARCH_API_KEY', "");
        Config::updateValue('_ACCELASEARCH_ONBOARDING', 0);
        Config::updateValue('_ACCELASEARCH_SHOPS_TO_SYNC', "[]");
        $this->success(true);
    }
}
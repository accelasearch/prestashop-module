<?php

namespace Accelasearch\Accelasearch\Controller\Test;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Controller\AbstractController;
use Accelasearch\Accelasearch\Controller\ControllerInterface;

class FinishOnBoardingController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        Config::updateValue('_ACCELASEARCH_ONBOARDING', 3);
        Config::updateValue('_ACCELASEARCH_CRONJOB_LASTEXEC', time());
        $this->success(true);
    }
}
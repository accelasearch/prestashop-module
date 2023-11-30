<?php

namespace Accelasearch\Accelasearch\Controller\Test;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Controller\AbstractController;
use Accelasearch\Accelasearch\Controller\ControllerInterface;
use Tools;

class SetOnBoardingController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        $onBoarding = Tools::getValue('onBoarding');
        Config::updateValue('_ACCELASEARCH_ONBOARDING', (int) $onBoarding);
        Config::updateValue('_ACCELASEARCH_API_KEY', "fycTY16AtSMF2BthV4gW7BNmrj5TCaWh");
        $this->success(true);
    }
}
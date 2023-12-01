<?php

namespace Accelasearch\Accelasearch\Controller\Test;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Controller\AbstractController;
use Accelasearch\Accelasearch\Controller\ControllerInterface;

class CreateExpiredLockController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        Config::updateValue('_ACCELASEARCH_FEED_LOCK', '1');
    }
}
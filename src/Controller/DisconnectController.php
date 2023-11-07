<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Config\Config;

class DisconnectController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        Config::initialize();
        $this->success(true);
    }
}
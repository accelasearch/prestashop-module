<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Config\Config;

class SetShopsController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        $args = $this->parseArgs();
        $shops = $args['shops'] ?? [];
        if (empty($shops)) {
            $this->error('No shops provided', 400);
        }
        Config::updateValue('_ACCELASEARCH_SHOPS_TO_SYNC', json_encode($shops));
        Config::updateValue('_ACCELASEARCH_ONBOARDING', 1);
        $this->success(true);
    }
}
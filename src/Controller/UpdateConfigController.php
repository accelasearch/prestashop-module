<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Config\Config;

class UpdateConfigController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        $args = $this->parseArgs();
        $configs = $args['configs'] ?? [];
        if (empty($configs)) {
            $this->error('No configs provided', 400);
        }
        foreach ($configs as $key => $value) {
            if (strpos($key, "_ACCELASEARCH_") !== 0) {
                continue;
            }
            Config::updateValue($key, $value);
        }
    }
}
<?php

namespace Accelasearch\Accelasearch\Sql\Version;

use Accelasearch\Accelasearch\Config\Config;
use Module;

abstract class UpdateAbstract
{
    protected $module;
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function updateVersion($version)
    {
        Config::updateValue('_ACCELASEARCH_UPGRADE_VERSION', $version);
    }

    abstract public function update();
}
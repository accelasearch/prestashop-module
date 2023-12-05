<?php

namespace Accelasearch\Accelasearch\Sql\Version;

use Accelasearch\Accelasearch\Config\Config;

class UpgradeModule_1_0_0 extends UpdateAbstract
{
    public function update()
    {
        Config::updateValue('_ACCELASEARCH_UPGRADE_TEST', 'OK');
    }
}
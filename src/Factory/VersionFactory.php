<?php

namespace Accelasearch\Accelasearch\Factory;

use Accelasearch\Accelasearch\Exception\UpgradeException;
use Accelasearch\Accelasearch\Sql\Version\UpgradeModule_1_0_0;

class VersionFactory
{
    const REGISTERED_VERSIONS = [
        "1.0.0" => UpgradeModule_1_0_0::class
    ];

    public static function create($version, $module)
    {
        if (!isset(self::REGISTERED_VERSIONS[$version])) {
            throw new UpgradeException("Version $version is not registered");
        }
        $class = self::REGISTERED_VERSIONS[$version];
        return new $class($module);
    }
}
<?php

namespace Accelasearch\Accelasearch\Sql;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Factory\VersionFactory;
use Module;

class Upgrade
{
    private $module;
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function getVersions()
    {
        return VersionFactory::REGISTERED_VERSIONS;
    }

    public function upgradeTo($version)
    {
        $updater = VersionFactory::create($version, $this->module);
        $updater->update();
        $updater->updateVersion($version);
    }

    public function upgrade()
    {
        $currentVersion = Config::get('_ACCELASEARCH_UPGRADE_VERSION', '0.0.0');
        $versions = $this->getVersions();
        foreach ($versions as $version => $class) {
            if (version_compare($version, $currentVersion, '>')) {
                $this->upgradeTo($version);
            }
        }
    }

}
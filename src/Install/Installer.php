<?php

namespace Accelasearch\Accelasearch\Install;

use Accelasearch\Accelasearch\Config\Config;
use Module;
use \Accelasearch\Accelasearch\Sql\Manager;

class Installer
{

    /**
     * List of hooks to register
     * @var array
     */
    const HOOKS = [
        'actionAdminControllerSetMedia',
    ];

    private $module;
    private $sqlManager;

    public function __construct(
        Module $module,
        Manager $sqlManager
    ) {
        $this->module = $module;
        $this->sqlManager = $sqlManager;
    }

    public function install()
    {
        if (!$this->registerHooks()) {
            return false;
        }

        if (!$this->installTables()) {
            return false;
        }

        if (!$this->installTab()) {
            return false;
        }

        if (!$this->initDefaultConfigurationValues()) {
            return false;
        }

        self::createTokens();

        return true;
    }

    public static function createTokens()
    {
        $feedToken = \Tools::passwdGen(16);
        $cronToken = \Tools::passwdGen(16);
        if (empty(Config::get("_ACCELASEARCH_FEED_RANDOM_TOKEN")))
            Config::updateValue('_ACCELASEARCH_FEED_RANDOM_TOKEN', $feedToken);
        if (empty(Config::get("_ACCELASEARCH_CRON_TOKEN")))
            Config::updateValue('_ACCELASEARCH_CRON_TOKEN', $cronToken);
    }

    public function uninstall()
    {
        if (!$this->unregisterHooks()) {
            return false;
        }

        if (!$this->uninstallTables()) {
            return false;
        }

        if (!$this->uninstallTab()) {
            return false;
        }
        return true;
    }

    private function installTables()
    {
        return $this->sqlManager->install();
    }

    private function uninstallTables()
    {
        return $this->sqlManager->uninstall();
    }

    private function registerHooks()
    {
        foreach (self::HOOKS as $hook) {
            if (!$this->module->registerHook($hook)) {
                return false;
            }
        }
        return true;
    }

    private function unregisterHooks()
    {
        foreach (self::HOOKS as $hook) {
            if (!$this->module->unregisterHook($hook)) {
                return false;
            }
        }
        return true;
    }

    # START ADMINCONTROLLER #
    private function installTab()
    {
        $languages = \Language::getLanguages();
        $tab = new \Tab();
        $tab->class_name = 'AccelasearchAdmin';
        $tab->module = $this->module->name;
        $tab->id_parent = (int) \Tab::getIdFromClassName('AdminCatalog');
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = 'Accelasearch';
        }
        try {
            $tab->save();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    private function uninstallTab()
    {
        $tab = (int) \Tab::getIdFromClassName('AccelasearchAdmin');
        if ($tab) {
            $mainTab = new \Tab($tab);
            try {
                $mainTab->delete();
            } catch (\Exception $e) {
                echo $e->getMessage();
                return false;
            }
        }
        return true;
    }
    # END ADMINCONTROLLER #



    /** Set module default configuration into database */
    private function initDefaultConfigurationValues()
    {
        foreach (Config::DEFAULT_CONFIGURATION as $key => $value) {
            if (!Config::get($key)) {
                Config::updateValue($key, $value);
            }
        }

        return true;
    }
}

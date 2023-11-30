<?php

namespace Accelasearch\Accelasearch\Entity;

use Accelasearch\Accelasearch\Config\Config;
use Shop;
use Db;

class Lock
{
    private $name;
    private $timestamp;
    public const CRITICAL_DURATION = 3600;

    public function __construct($name)
    {
        $this->name = $name;
        $this->timestamp = time();
    }

    public function create()
    {
        Shop::setContext(Shop::CONTEXT_ALL);
        return Config::updateValue("_ACCELASEARCH_" . $this->name . "_LOCK", $this->timestamp);
    }

    public function delete()
    {
        Shop::setContext(Shop::CONTEXT_ALL);
        return Config::deleteByName("_ACCELASEARCH_" . $this->name . "_LOCK");
    }

    public function isLocked(): bool
    {
        Shop::setContext(Shop::CONTEXT_ALL);
        return (bool) Config::get("_ACCELASEARCH_" . $this->name . "_LOCK", false);
    }

    public static function getLocks()
    {
        $_locks = Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "configuration WHERE name LIKE '_ACCELASEARCH_%_LOCK'");
        $locks = [];
        foreach ($_locks as $lock) {
            $name = str_replace("_ACCELASEARCH_", "", $lock["name"]);
            $name = str_replace("_LOCK", "", $name);
            $locks[] = [
                "name" => $name,
                "value" => $lock["value"],
            ];
        }
        return $locks;
    }

    public static function getExpiredLocks()
    {
        $locks = self::getLocks();
        $locks = array_filter($locks, function ($lock) {
            return $lock["value"] < time() - self::CRITICAL_DURATION;
        });
        return $locks;
    }
}
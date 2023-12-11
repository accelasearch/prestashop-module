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
        return Config::updateValue("_ACCELASEARCH_" . $this->name . "_LOCK", $this->timestamp);
    }

    public function delete()
    {
        return Config::deleteByName("_ACCELASEARCH_" . $this->name . "_LOCK");
    }

    public function isLocked(): bool
    {
        return (bool) Config::get("_ACCELASEARCH_" . $this->name . "_LOCK", false);
    }

    public static function getLocks($db = null)
    {
        if ($db === null) {
            $db = Db::getInstance();
        }
        $_locks = $db->executeS("SELECT * FROM " . _DB_PREFIX_ . "configuration WHERE name LIKE '_ACCELASEARCH_%_LOCK'");
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

    public static function getExpiredLocks($db = null)
    {
        $locks = self::getLocks($db);
        $locks = array_filter($locks, function ($lock) {
            return $lock["value"] < time() - self::CRITICAL_DURATION;
        });
        return $locks;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $timestamp
     * @return self
     */
    public function setTimestamp($timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return self
     */
    public function setName($name): self
    {
        $this->name = $name;
        return $this;
    }
}

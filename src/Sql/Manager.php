<?php

namespace Accelasearch\Accelasearch\Sql;

class Manager
{

    const SQL_INSTALL = "install";
    const SQL_UNINSTALL = "uninstall";

    /**
     * Get SQL from file
     * @param string $name self::SQL_INSTALL or self::SQL_UNINSTALL
     * @return string|false SQL string or false if file not found
     */
    public function getSql(string $name)
    {
        return file_get_contents(dirname(__FILE__) . "/$name.sql");
    }

    public function install()
    {
        $sql = $this->getSql(self::SQL_INSTALL);
        if (!$sql) return false;
        // replace prefix
        $sql = str_replace("__DB_PREFIX__", _DB_PREFIX_, $sql);
        return \Db::getInstance()->execute($sql);
    }

    public function uninstall()
    {
        $sql = $this->getSql(self::SQL_UNINSTALL);
        if (!$sql) return false;
        $sql = str_replace("__DB_PREFIX__", _DB_PREFIX_, $sql);
        return \Db::getInstance()->execute($sql);
    }
}

<?php

namespace Accelasearch\Accelasearch\Logger;

class Log
{
    const CRITICAL = 0;
    const ERROR = 1;
    const WARNING = 2;
    const INFO = 3;

    const CONTEXT_GENERAL = "GENERAL";
    const CONTEXT_PRODUCT_FEED_CREATION = "PRODUCT_FEED_CREATION";
    const CONTEXT_CRONJOB = "CRONJOB";
    const CONTEXT_ACCELASEARCH_API = "ACCELASEARCH_API";
    const ENABLED = true;

    /**
     * Write a log
     *
     * Write a log inside accelasearch_logs table
     *
     * @param string $msg Message to write
     * @param self::CRITICAL|self::ERROR|self::WARNING|self::INFO|int $gravity A number that represents the gravity of the log, prefer to use the constants of the class but you can use custom values
     * @param self::CONTEXT_GENERAL|self::CONTEXT_PRODUCT_CREATION|self::CONTEXT_PRODUCT_UPDATE|self::CONTEXT_CRONJOB|string $context A string that represents the context of the log, prefer to use the constants of the class but you can use custom values
     * 
     * @return int ID of the log
     **/
    public static function write(string $msg, $gravity = self::CRITICAL, $context = self::CONTEXT_GENERAL, $line = null, $file = null)
    {
        if (!self::ENABLED)
            return 0;
        if ($line !== null)
            $msg .= " in line $line";
        if ($file !== null)
            $msg .= " on file $file";
        \Db::getInstance()->insert("accelasearch_logs", [
            "message" => $msg,
            "gravity" => $gravity,
            "context" => $context
        ]);
        return \Db::getInstance()->Insert_ID();
    }

    public static function getLogs(int $limit = 10000)
    {
        return \Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "accelasearch_logs ORDER BY id DESC LIMIT $limit");
    }

    public static function truncateLogs()
    {
        return \Db::getInstance()->delete("accelasearch_logs", "1");
    }
}
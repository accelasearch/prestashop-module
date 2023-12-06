<?php

namespace Accelasearch\Accelasearch\Logger;

use Db;

class Log
{
    public const CRITICAL = 0;
    public const ERROR = 1;
    public const WARNING = 2;
    public const INFO = 3;

    public const CONTEXT_GENERAL = "GENERAL";
    public const CONTEXT_PRODUCT_FEED_CREATION = "PRODUCT_FEED_CREATION";
    public const CONTEXT_CRONJOB = "CRONJOB";
    public const CONTEXT_ACCELASEARCH_API = "ACCELASEARCH_API";
    public const ENABLED = true;

    /**
     * Write a log
     *
     * Write a log inside accelasearch_logs table
     *
     * @param string $msg Message to write
     * @param self::CRITICAL|self::ERROR|self::WARNING|self::INFO|int $gravity A number that represents the gravity of the log, prefer to use the constants of the class but you can use custom values
     * @param self::CONTEXT_GENERAL|self::CONTEXT_PRODUCT_CREATION|self::CONTEXT_PRODUCT_UPDATE|self::CONTEXT_CRONJOB|string $context A string that represents the context of the log, prefer to use the constants of the class but you can use custom values
     *
     * @return string Generated message
     **/
    public static function write(string $msg, $gravity = self::CRITICAL, $context = self::CONTEXT_GENERAL, $line = null, $file = null)
    {
        // @phpstan-ignore-next-line
        if(self::ENABLED === false) {
            return "";
        }
        if($line !== null) {
            $msg .= " in line $line";
        }
        if($file !== null) {
            $msg .= " on file $file";
        }
        Db::getInstance()->insert("accelasearch_logs", [
            "message" => $msg,
            "gravity" => $gravity,
            "context" => $context
        ]);
        return php_sapi_name() !== "cli" ? $msg : "";
    }

    public static function getLogs(int $limit = 10000)
    {
        return Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "accelasearch_logs ORDER BY id DESC LIMIT $limit");
    }

    public static function truncateLogs()
    {
        return Db::getInstance()->delete("accelasearch_logs", "1");
    }
}

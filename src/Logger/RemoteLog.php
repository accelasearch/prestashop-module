<?php

namespace Accelasearch\Accelasearch\Logger;

use Accelasearch\Accelasearch\Api\DgcalClient;

class RemoteLog extends Log
{
    public static function write(string $msg, $gravity = self::CRITICAL, $context = self::CONTEXT_GENERAL, $line = null, $file = null)
    {
        parent::write($msg, $gravity, $context, $line, $file);
        DgcalClient::createLog($msg, $gravity, $context);
        return php_sapi_name() !== "cli" ? $msg : "";
    }
}

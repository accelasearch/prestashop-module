<?php

namespace Accelasearch\Accelasearch\Logger;

class RemoteLog extends Log
{
    public static function write(string $msg, $gravity = self::CRITICAL, $context = self::CONTEXT_GENERAL, $line = null, $file = null)
    {
        parent::write($msg, $gravity, $context, $line, $file);
        //TODO: Add remote log part via api

        return 0;
    }
}
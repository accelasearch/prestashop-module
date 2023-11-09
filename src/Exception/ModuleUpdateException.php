<?php

namespace Accelasearch\Accelasearch\Exception;

class ModuleUpdateException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
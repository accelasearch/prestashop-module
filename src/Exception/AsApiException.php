<?php

namespace Accelasearch\Accelasearch\Exception;

class AsApiException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct("An error occurred during Accelasearch API call: " . $message);
    }
}
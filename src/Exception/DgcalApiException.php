<?php

namespace Accelasearch\Accelasearch\Exception;

class DgcalApiException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct("An error occurred during DGCAL API call: " . $message);
    }
}
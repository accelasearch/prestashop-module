<?php

namespace Accelasearch\Accelasearch\Exception;

class LanguageNotFoundException extends \Exception
{
    public function __construct(int $id)
    {
        parent::__construct("Language not found: $id");
    }
}
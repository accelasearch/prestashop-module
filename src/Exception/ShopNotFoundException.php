<?php

namespace Accelasearch\Accelasearch\Exception;

class ShopNotFoundException extends \Exception
{
    public function __construct(int $id)
    {
        parent::__construct("Shop not found: $id");
    }
}
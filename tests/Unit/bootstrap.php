<?php

require __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/MockProxy.php';

// Fake pSQL function
function pSQL($string, $htmlOK = false)
{
    return $string;
}
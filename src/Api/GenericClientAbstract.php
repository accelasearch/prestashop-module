<?php

namespace Accelasearch\Accelasearch\Api;

use GuzzleHttp\Client as GuzzleClient;

abstract class GenericClientAbstract
{
    private static $instance = null;
    private $client;

    protected function __construct($defaults = [])
    {
        $this->client = new GuzzleClient($defaults);
    }

    abstract public static function getInstance();

    abstract protected static function getDefaults();
}
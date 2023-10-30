<?php

namespace Accelasearch\Accelasearch\Config;

class Config
{
    const DGCAL_ENDPOINT = 'https://accelaserch.dgcal.it/api/v1/';
    const FEED_OUTPUT_PATH = "public/feed/";

    const DEFAULT_CONFIGURATION = [
        "_ACCELASEARCH_SYNCTYPE" => "CONFIGURABLE_WITH_SIMPLE",
        "_ACCELASEARCH_FEED_RANDOM_TOKEN" => "",
    ];

    public static function get($key)
    {
        return \Configuration::get($key);
    }

    public static function updateValue($key, $value)
    {
        return \Configuration::updateValue($key, $value);
    }

    public static function deleteByName($key)
    {
        return \Configuration::deleteByName($key);
    }
}
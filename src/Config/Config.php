<?php

namespace Accelasearch\Accelasearch\Config;

class Config
{
    const DGCAL_ENDPOINT = 'https://accelaserch.dgcal.it/api/v1/';
    const FEED_OUTPUT_PATH = "public/feed/";

    const DEFAULT_CONFIGURATION = [
        "_ACCELASEARCH_SYNCTYPE" => "CONFIGURABLE_WITH_SIMPLE",
        "_ACCELASEARCH_FEED_RANDOM_TOKEN" => "",
        "_ACCELASEARCH_COLOR_LABEL" => "color",
        "_ACCELASEARCH_SIZE_LABEL" => "size",
    ];

    public static function get($key, $default = false)
    {
        return \Configuration::get($key, null, null, null, $default);
    }

    public static function updateValue($key, $value)
    {
        return \Configuration::updateValue($key, $value);
    }

    public static function deleteByName($key)
    {
        return \Configuration::deleteByName($key);
    }

    public static function getColorLabel()
    {
        return self::get("_ACCELASEARCH_COLOR_LABEL", "color");
    }

    public static function getSizeLabel()
    {
        return self::get("_ACCELASEARCH_SIZE_LABEL", "size");
    }

    /**
     * Initial configuration loaded in backoffice page to render the react app with specific behaviour
     */
    public static function getBackofficeConfig()
    {
        return [
            "userStatus" => [
                "logged" => true,
                "onBoarding" => 0
            ]
        ];
    }
}
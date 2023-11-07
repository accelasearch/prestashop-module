<?php

namespace Accelasearch\Accelasearch\Config;

use Accelasearch\Accelasearch\Api\AsClient;
use Accelasearch\Accelasearch\Install\Installer;

class Config
{
    const DGCAL_ENDPOINT = 'https://dgcal-dev.it/accelasearch/api/v1/';
    const ACCELASEARCH_ENDPOINT = 'https://svc11.accelasearch.net/API/';
    const FEED_OUTPUT_PATH = "public/feed/";

    /**
     * Possible values used for Configuration ( ps_configuration )
     */
    const DEFAULT_CONFIGURATION = [
        "_ACCELASEARCH_SYNCTYPE" => "CONFIGURABLE_WITH_SIMPLE",
        "_ACCELASEARCH_FEED_RANDOM_TOKEN" => "",
        "_ACCELASEARCH_CRON_TOKEN" => "",
        "_ACCELASEARCH_COLOR_ID" => 0,
        "_ACCELASEARCH_SIZE_ID" => 0,
        "_ACCELASEARCH_API_KEY" => "",
        "_ACCELASEARCH_API_COLLECTOR" => "",
        "_ACCELASEARCH_SHOPS_TO_SYNC" => "[]",
        "_ACCELASEARCH_ONBOARDING" => 0,
        "_ACCELASEARCH_CRONJOB_LASTEXEC" => 0,
    ];

    public static function initialize()
    {
        foreach (self::DEFAULT_CONFIGURATION as $key => $value) {
            \Configuration::updateValue($key, $value);
        }
        Installer::createTokens();
    }

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

    public static function getColorLabel($id_lang = null)
    {
        $id = self::get("_ACCELASEARCH_COLOR_ID", 0);
        if (empty($id))
            return "color";
        $attributeGroup = new \AttributeGroup($id, $id_lang);
        return $attributeGroup->name;
    }

    public static function getSizeLabel($id_lang = null)
    {
        $id = self::get("_ACCELASEARCH_SIZE_ID", 0);
        if (empty($id))
            return "size";
        $attributeGroup = new \AttributeGroup($id, $id_lang);
        return $attributeGroup->name;
    }

    public static function getShopsToSync()
    {
        $shops = self::get("_ACCELASEARCH_SHOPS_TO_SYNC", []);
        return json_decode($shops);
    }

    public static function getLastExecLocale()
    {
        $lastExec = self::get("_ACCELASEARCH_CRONJOB_LASTEXEC", 0);
        if (empty($lastExec))
            return "never";
        return date("d/m/Y H:i:s", $lastExec);
    }

    /**
     * Initial configuration loaded in backoffice page to render the react app with specific behaviour
     */
    public static function getBackofficeConfig()
    {
        $apiKey = self::get("_ACCELASEARCH_API_KEY");
        $logged = AsClient::apiKeyVerify($apiKey);
        return [
            "userStatus" => [
                "moduleDir" => _PS_MODULE_DIR_ . "accelasearch/",
                "logged" => $logged,
                "onBoarding" => (int) self::get("_ACCELASEARCH_ONBOARDING", 0),
                "syncType" => self::get("_ACCELASEARCH_SYNCTYPE", "CONFIGURABLE_WITH_SIMPLE"),
                "attributes" => [
                    "color" => [
                        "label" => self::getColorLabel(),
                        "id" => self::get("_ACCELASEARCH_COLOR_ID", 0)
                    ],
                    "size" => [
                        "label" => self::getSizeLabel(),
                        "id" => self::get("_ACCELASEARCH_SIZE_ID", 0)
                    ]
                ],
                "shops" => self::getShopsToSync(),
                "lastExec" => self::getLastExecLocale(),
                "exportedProductsCount" => 123456
            ]
        ];
    }
}
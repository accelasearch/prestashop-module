<?php

namespace Accelasearch\Accelasearch\Entity;

use Accelasearch\Accelasearch\Config\Config;
use AccelaSearch\ProductMapper\Api\Client;
use AccelaSearch\ProductMapper\DataMapper\Sql\Shop as ShopMapper;
use AccelaSearch\ProductMapper\DataMapper\Api\Collector as CollectorMapper;
use AccelaSearch\ProductMapper\Shop;
use AccelaSearch\ProductMapper\Cms;
use PDO;

class AsShop
{
    private static $shop_mapper;

    public static function setupShopMapper()
    {
        $apiKey = Config::get("_ACCELASEARCH_API_KEY");
        if (empty($apiKey)) {
            throw new \Exception("No API key found");
        }
        $client = Client::fromApiKey($apiKey);
        $collector_mapper = new CollectorMapper($client);
        $collector = $collector_mapper->read();

        $dbh = new PDO(
            'mysql:host=' . $collector->getHostName() . ';dbname=' . $collector->getDatabaseName(),
            $collector->getUsername(),
            $collector->getPassword(),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        self::$shop_mapper = ShopMapper::fromConnection($dbh);
    }


    public static function create(string $url, string $iso, string $feedUrl = "")
    {
        if (self::$shop_mapper === null) {
            self::setupShopMapper();
        }
        $cms = new Cms(55, "Prestashop Module", "1.0");
        $shop = new Shop($url, $iso, $cms);
        $shop->setCmsData([
            "feedUrl" => $feedUrl
        ]);
        $shop->setIsActive(true);
        self::$shop_mapper->create($shop);
    }

    public static function getShops()
    {
        if (self::$shop_mapper === null) {
            self::setupShopMapper();
        }
        return self::$shop_mapper->search();
    }
}
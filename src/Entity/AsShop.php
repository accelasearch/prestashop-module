<?php

namespace Accelasearch\Accelasearch\Entity;

use Accelasearch\Accelasearch\Api\AsClient;
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

    public static function getDbh()
    {
        $apiKey = Config::get("_ACCELASEARCH_API_KEY");
        if(empty($apiKey)) {
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
        return $dbh;
    }

    public static function setupShopMapper()
    {
        $dbh = self::getDbh();
        self::$shop_mapper = ShopMapper::fromConnection($dbh);
    }

    public static function softDeleteAllProducts($as_shop_id)
    {
        $dbh = self::getDbh();

        // tables with a relation of products
        $tables = [
            "products_attr_datetime",
            "products_attr_float",
            "products_attr_int",
            "products_attr_str",
            "products_attr_text",
            "products_children",
            "stocks",
            "prices",
            "products_categories",
            "products_images",
        ];

        foreach($tables as $table) {
            $sql = "
            UPDATE $table tb 
            LEFT JOIN products p ON p.id = tb.productid
            LEFT JOIN storeviews s ON s.id = p.siteid
            SET tb.deleted = 1
            WHERE s.cmsdata LIKE '%\"id\":$as_shop_id%'
            ";
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
        }

        // tables with a relation of storeviews
        $tables = [
            "products_attr_label",
            "products_images_lbl",
            "warehouses"
        ];

        foreach($tables as $table) {
            $sql = "
            UPDATE $table tb 
            LEFT JOIN storeviews s ON s.id = tb.storeviewid
            SET tb.deleted = 1
            WHERE s.cmsdata LIKE '%\"id\":$as_shop_id%'
            ";
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
        }


        $sql = "
        UPDATE products p
        LEFT JOIN storeviews s ON s.id = p.siteid
        SET p.deleted = 1
        WHERE s.cmsdata LIKE '%\"id\":$as_shop_id%'
        ";

        $stmt = $dbh->prepare($sql);
        $stmt->execute();
    }

    public static function create(string $url, string $iso)
    {
        if(self::$shop_mapper === null) {
            self::setupShopMapper();
        }
        $cms = new Cms(60, "Prestashop Module", "1.0");
        $shop = new Shop($url, $iso, $cms);
        $shop->setIsActive(true);
        self::$shop_mapper->create($shop);
        return $shop->getIdentifier();
    }

    public static function getShops()
    {
        if(self::$shop_mapper === null) {
            self::setupShopMapper();
        }
        return self::$shop_mapper->search();
    }

    public static function getByUrl(string $url)
    {
        $shops = self::getShops();
        foreach($shops as $shop) {
            if($shop->getUrl() === $url) {
                return $shop;
            }
        }
        return null;
    }

    public static function getRealIdByIdShopAndIdLang($id_shop, $id_lang)
    {
        $shops = json_decode(Config::get("_ACCELASEARCH_SHOPS_TO_SYNC"), true);
        foreach($shops as $shop) {
            if((int)$shop['id_shop'] === (int)$id_shop && (int)$shop['id_lang'] === (int)$id_lang) {
                return (int) $shop['id_shop_as'];
            }
        }
        return false;
    }

    public static function updateFeedUrlByIdShopAndIdLang($id_shop, $id_lang, $feedUrl)
    {
        $id_shop_as = self::getRealIdByIdShopAndIdLang($id_shop, $id_lang);
        if($id_shop_as === false) {
            throw new \Exception("No id_shop_as found for id_shop $id_shop and id_lang $id_lang");
        }
        $data = [
            "id" => $id_shop_as,
            "apiKey" => Config::get("_ACCELASEARCH_API_KEY"),
            "feedUrl" => $feedUrl,
            "lastSynchronization" => null,
        ];
        AsClient::updateCmsDataByRealId($id_shop_as, $data);
    }
}

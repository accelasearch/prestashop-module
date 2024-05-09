<?php

namespace Accelasearch\Accelasearch\Entity;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Exception\ShopNotFoundException;
use Context;
use Tools;
use DbQuery;

class Shop
{
    private $id;
    public $ps;
    private $context;
    public function __construct(int $id, Context $context = null)
    {
        if (!is_array(\Shop::getShop($id))) {
            throw new ShopNotFoundException($id);
        }
        $this->id = $id;
        $this->ps = new \Shop($id);
        $this->context = $context;
    }
    public function getId(): int
    {
        return $this->id;
    }

    public function getAsShopId()
    {
        $shops = json_decode(Config::get("_ACCELASEARCH_SHOPS_TO_SYNC"), true);
        foreach ($shops as $shop) {
            $as_shop_id = (int) $shop['id_shop_as'] ?? 0;
            $id_shop = (int) $shop['id_shop'];
            if ($id_shop === $this->id) {
                return $as_shop_id;
            }
        }
        return 0;
    }

    public static function getShopsInfo()
    {
        \Shop::setContext(\Shop::CONTEXT_ALL);
        $shops = \Shop::getShops(true);
        $languages = \Language::getLanguages(true);

        foreach ($shops as $key => $shop) {
            $available_languages = [];
            foreach ($languages as $language) {
                if ($language['shops'][$shop['id_shop']] === true) {
                    $id_lang = $language['id_lang'];
                    $id_shop = $shop['id_shop'];
                    $flagIcon = Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . 'img/tmp/lang_mini_' . $id_lang . '_' . $id_shop . '.jpg';
                    $language['flagIcon'] = $flagIcon;
                    $available_languages[] = $language;
                }
            }
            $shops[$key]['languages'] = $available_languages;
        }
        return $shops;
    }

    public function getLangLink($idLang = null, Context $context = null, $idShop = null)
    {
        static $psRewritingSettings = null;
        if ($psRewritingSettings === null) {
            $psRewritingSettings = (int) \Configuration::get('PS_REWRITING_SETTINGS', null);
        }
        if (!$context) {
            $context = Context::getContext();
        }
        if (!\Language::isMultiLanguageActivated($idShop) || !$psRewritingSettings) {
            return '';
        }
        if (!$idLang) {
            $idLang = $context->language->id;
        }

        $iso = \Language::getIsoById($idLang);
        if ($iso === "gb") {
            $iso = "en";
        }

        return $iso . '/';
    }

    public function getUrl($id_lang)
    {
        $base = $this->context->link->getBaseLink($this->id);
        $langLink = $this->getLangLink($id_lang, $this->context, $this->id);
        return $base . $langLink;
    }

    public function getHash($id_lang)
    {
        $url = $this->getUrl($id_lang);
        $iso = $this->context->language->iso_code;
        if ($iso === "gb") {
            $iso = "en";
        }
        return md5($url . $iso);
    }

    public static function getTotalRevenue()
    {
        $dbQuery = new DbQuery();
        $dbQuery->select('SUM(total_paid_tax_incl)');
        $dbQuery->from('orders');
        $dbQuery->where('current_state = ' . Config::get('PS_OS_PAYMENT'));
        $dbQuery->where('valid = 1');

        return \Db::getInstance()->getValue($dbQuery);
    }

    public static function getTotalOrders()
    {
        $dbQuery = new DbQuery();
        $dbQuery->select('COUNT(*)');
        $dbQuery->from('orders');
        $dbQuery->where('valid = 1');

        return \Db::getInstance()->getValue($dbQuery);
    }

    public static function getTotalProducts()
    {
        $dbQuery = new DbQuery();
        $dbQuery->select('COUNT(*)');
        $dbQuery->from('product');

        return \Db::getInstance()->getValue($dbQuery);
    }

    public static function getTotalCustomers()
    {
        $dbQuery = new DbQuery();
        $dbQuery->select('COUNT(*)');
        $dbQuery->from('customer');

        return \Db::getInstance()->getValue($dbQuery);
    }

    public static function getMainShopInfo()
    {
        $shop_url = Tools::getShopDomainSsl(true, true);
        $shop_name = Config::get('PS_SHOP_NAME');
        $metadata = [
            'prestashop_version' => _PS_VERSION_,
            'php_version' => phpversion(),
            'mysql_version' => \Db::getInstance()->getVersion(),
            'php_memory_limit' => ini_get('memory_limit'),
            'php_max_execution_time' => ini_get('max_execution_time'),
            'php_max_input_vars' => ini_get('max_input_vars'),
            'php_post_max_size' => ini_get('post_max_size'),
            'order_revenue' => self::getTotalRevenue(),
            'order_count' => self::getTotalOrders(),
            'product_count' => self::getTotalProducts(),
            'customer_count' => self::getTotalCustomers(),
        ];
        return [
            'shop_url' => $shop_url,
            'shop_name' => $shop_name,
            'shop_metadata' => $metadata,
        ];
    }
}

<?php

namespace Accelasearch\Accelasearch\Entity;

use Accelasearch\Accelasearch\Exception\ShopNotFoundException;

class Shop
{
    private $id;
    public $ps;
    public function __construct(int $id)
    {
        if (\Shop::getShop($id) === false)
            throw new ShopNotFoundException($id);
        $this->id = $id;
        $this->ps = new \Shop($id);
    }
    public function getId(): int
    {
        return $this->id;
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
                    $flagIcon = \Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . 'img/tmp/lang_mini_' . $id_lang . '_' . $id_shop . '.jpg';
                    $language['flagIcon'] = $flagIcon;
                    $available_languages[] = $language;
                }
            }
            $shops[$key]['languages'] = $available_languages;
        }
        return $shops;
    }
}
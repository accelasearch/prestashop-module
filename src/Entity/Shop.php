<?php

namespace Accelasearch\Accelasearch\Entity;

use Accelasearch\Accelasearch\Exception\ShopNotFoundException;
use Context;

class Shop
{
    private $id;
    public $ps;
    private $context;
    public function __construct(int $id, Context $context = null)
    {
        if (!is_array(\Shop::getShop($id)))
            throw new ShopNotFoundException($id);
        $this->id = $id;
        $this->ps = new \Shop($id);
        $this->context = $context;
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

    public function getLangLink($idLang = null, Context $context = null, $idShop = null)
    {
        static $psRewritingSettings = null;
        if ($psRewritingSettings === null) {
            $psRewritingSettings = (int) \Configuration::get('PS_REWRITING_SETTINGS', null, null, $idShop);
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

        return \Language::getIsoById($idLang) . '/';
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
        return md5($url . $iso);
    }
}
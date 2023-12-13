<?php

namespace Accelasearch\Accelasearch\Command;

use Accelasearch\Accelasearch\Api\AsClient;
use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Decorator\ProductDecorator;
use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Entity\Shop;
use Accelasearch\Accelasearch\Factory\ProductDataFactory;
use Accelasearch\Accelasearch\Formatter\ArrayFormatter;
use Accelasearch\Accelasearch\Logger\Log;
use Accelasearch\Accelasearch\Repository\CategoryRepository;
use Accelasearch\Accelasearch\Repository\ProductRepository;

/**
 * This class provides a static method to generate a feed by shop and language IDs.
 * It sets the context, currency, and repositories needed to create a product service.
 * Finally, it generates the feed with debug mode enabled.
 */
class FeedFacade
{
    public static function generateByIdShopAndIdLang(int $id_shop, int $id_lang, $output = null)
    {

        Log::write("Generating feed started for shop $id_shop and language $id_lang", Log::INFO, Log::CONTEXT_PRODUCT_FEED_CREATION);

        $shop = new Shop($id_shop, \Context::getContext());
        $language = new Language($id_lang);

        \Shop::setContext(\Shop::CONTEXT_SHOP, $id_shop);
        \Context::getContext()->shop = $shop->ps;

        $currency = new \Currency(Config::get("PS_CURRENCY_DEFAULT"));
        \Context::getContext()->currency = $currency;

        $productRepository = new ProductRepository(\Db::getInstance(), \Context::getContext());
        $categoryRepository = new CategoryRepository(\Db::getInstance(), \Context::getContext());

        $productService = ProductDataFactory::create(
            $productRepository,
            new ProductDecorator(
                $productRepository,
                $shop,
                $language,
                new ArrayFormatter(),
                \Context::getContext(),
                $categoryRepository
            ),
            new Config(),
            Config::get("_ACCELASEARCH_SYNCTYPE")
        );

        $feed = new Feed($shop, $language, $productService);
        $feed->setDebug(true);
        $feed->generate($output);

        if ((int) Config::get("_ACCELASEARCH_DELETE_SYNC") === 1) {
            try {
                AsClient::deleteSync($shop->getAsShopId());
            } catch (\Exception $e) {
                Log::write("Error deleting sync for shop $id_shop and language $id_lang", Log::ERROR, Log::CONTEXT_PRODUCT_FEED_CREATION);
            } finally {
                Config::deleteByName("_ACCELASEARCH_DELETE_SYNC");
            }
        }

        Log::write("Feed generated", Log::INFO, Log::CONTEXT_PRODUCT_FEED_CREATION);
    }
}

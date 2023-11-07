<?php

namespace Accelasearch\Accelasearch\Cron\Operation;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Decorator\ProductDecorator;
use Accelasearch\Accelasearch\Entity\Shop;
use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Command\Feed;
use Accelasearch\Accelasearch\Factory\ProductDataFactory;
use Accelasearch\Accelasearch\Formatter\ArrayFormatter;
use Accelasearch\Accelasearch\Repository\CategoryRepository;
use Accelasearch\Accelasearch\Repository\ProductRepository;

class FeedGeneration extends OperationAbstract
{
    public function execute()
    {
        $this->lock();

        $shops = Config::getShopsToSync();
        @\Context::getContext()->controller->controller_type = 'front';

        foreach ($shops as $shop) {
            $id_shop = $shop->id_shop;
            $id_lang = $shop->id_lang;

            $shop = new Shop($id_shop);
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
            $feed->generate();
        }

        $this->updateExecutionTime();
        $this->unlock();
        echo "Done.";
    }

    public function getUpdateTiming(): int
    {
        return 60 * 15;
    }
}
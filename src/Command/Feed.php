<?php

namespace Accelasearch\Accelasearch\Command;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Entity\Shop;
use Accelasearch\Accelasearch\Factory\ProductBuilderFactory;
use Accelasearch\Accelasearch\Logger\Log;
use Accelasearch\Accelasearch\Logger\RemoteLog;
use Accelasearch\Accelasearch\Service\ServiceInterface;
use Vitalybaev\GoogleMerchant\Feed as GoogleShoppingFeed;
use Vitalybaev\GoogleMerchant\Product as GoogleShoppingProduct;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Accelasearch\Accelasearch\Entity\AsShop;

class Feed
{
    private $shop;
    private $language;
    /**
     * @var bool
     */
    private $debug = false;
    private $execution_time = 0;
    private $memory_used = 0;
    private $productService;
    private $feed;
    private $filesystem;
    private $configurable_ids = [];
    public function __construct(Shop $shop, Language $language, ServiceInterface $productService, GoogleShoppingFeed $feed)
    {
        $this->shop = $shop;
        $this->language = $language;
        $this->productService = $productService;
        $this->feed = $feed;
        $this->filesystem = new Filesystem();
    }

    public function isConfigurableCreated($id_product)
    {
        return in_array($id_product, $this->configurable_ids);
    }

    public function generate($output = null)
    {

        $start = microtime(true);
        $memory = memory_get_usage(true);

        $progressIndicator = null;
        $progressBar = null;

        if (php_sapi_name() === "cli") {
            $progressIndicator = new ProgressIndicator($output);
            $progressIndicator->start("Getting products from Database");
        }

        $totalProducts = $this->productService->getProductsNb($this->shop->getId(), $this->language->getId());
        $totalProcessed = 0;
        $iteration_number = 0;

        Log::write("Getting $totalProducts products info from Database", Log::INFO, Log::CONTEXT_PRODUCT_FEED_CREATION);

        do {
            $products = $this->productService->getProducts($this->shop, $this->language, $iteration_number * 10000, 10000, $progressIndicator);
            $totalProcessed += count($products);
            $iteration_number++;
            $totalProducts -= 10000;
            if (php_sapi_name() === "cli") {
                $progressIndicator->finish(count($products) . " Products retrieved");
                echo "\n\n";
            }

            Log::write(count($products) . " Products retrieved - " . $iteration_number + 1 . " iteration", Log::INFO, Log::CONTEXT_PRODUCT_FEED_CREATION);

            if (php_sapi_name() === "cli") {
                $progressBar = new ProgressBar($output, count($products));
                $progressBar->start();
            }

            foreach ($products as $product) {

                $item = new GoogleShoppingProduct();
                $feedProduct = ProductBuilderFactory::create(
                    $product,
                    $item,
                    Config::get("_ACCELASEARCH_SYNCTYPE")
                );
                $feedProduct->build($this->shop, $this->language);
                $this->feed->addProduct($feedProduct->getItem());

                if (php_sapi_name() === "cli")
                    $progressBar->advance();
            }
        } while ($totalProducts > 0);

        if (php_sapi_name() === "cli")
            $progressBar->finish();

        if (php_sapi_name() === "cli")
            echo "\n\n";

        $end = microtime(true);
        $memory = memory_get_usage(true) - $memory;

        $this->execution_time = ($end - $start);
        $this->memory_used = ($memory / 1024 / 1024);

        if ($this->debug) {
            echo "Time: " . ($end - $start) . "\n";
            echo "Memory: " . ($memory / 1024 / 1024) . " MB\n";
        }

        echo "Products: " . $totalProcessed . "\n";

        try {
            $this->filesystem->dumpFile(
                $this->getOutputPath(),
                $this->feed->build()
            );
            $url = $this->shop->getUrl($this->language->getId());
            $asShop = AsShop::getByUrl($url);
            if ($asShop !== null) {
                AsShop::updateFeedUrlByShop($asShop, $this->getFeedUrl());
            }
        } catch (IOExceptionInterface $exception) {
            $message = "An error occurred while creating your feed at " . $exception->getPath() . "\n" . $exception->getMessage() . "\n";
            RemoteLog::write($message, Log::ERROR, Log::CONTEXT_PRODUCT_FEED_CREATION);
            echo $message;
        }

        Log::write("Feed generated in " . $this->execution_time . ", memory used: " . $this->memory_used, Log::INFO, Log::CONTEXT_PRODUCT_FEED_CREATION);
        echo "Feed generated at " . $this->getOutputPath() . "\n\n";
        return $this->getFeedUrl();
    }

    /**
     * 
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * 
     * @param bool $debug 
     * @return self
     */
    public function setDebug($debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    public function getOutputPath()
    {
        return _PS_MODULE_DIR_ . 'accelasearch/' . Config::FEED_OUTPUT_PATH . Config::get("_ACCELASEARCH_FEED_RANDOM_TOKEN") . "-" . $this->shop->getId() . '_' . $this->language->getId() . '.xml';
    }

    public function getFeedUrl()
    {
        return $this->shop->getUrl($this->language->getId()) . 'modules/accelasearch/' . Config::FEED_OUTPUT_PATH . Config::get("_ACCELASEARCH_FEED_RANDOM_TOKEN") . "-" . $this->shop->getId() . '_' . $this->language->getId() . '.xml';
    }
}
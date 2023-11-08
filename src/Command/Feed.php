<?php

namespace Accelasearch\Accelasearch\Command;

use Accelasearch\Accelasearch\Builder\ProductBuilder;
use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Entity\Shop;
use Accelasearch\Accelasearch\Factory\ContextFactory;
use Accelasearch\Accelasearch\Factory\ProductBuilderFactory;
use Accelasearch\Accelasearch\Logger\Log;
use Accelasearch\Accelasearch\Service\ServiceInterface;
use Vitalybaev\GoogleMerchant\Feed as GoogleShoppingFeed;
use Vitalybaev\GoogleMerchant\Product as GoogleShoppingProduct;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

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
    public function __construct(Shop $shop, Language $language, ServiceInterface $productService)
    {
        $this->shop = $shop;
        $this->language = $language;
        $this->productService = $productService;
        $this->feed = new GoogleShoppingFeed(
            $shop->ps->name . " - " . $language->ps->name,
            ContextFactory::getContext()->link->getBaseLink($shop->getId()),
            "Google Shopping Feed for " . $shop->ps->name . " - " . $language->ps->name
        );
        $this->filesystem = new Filesystem();
    }

    public function isConfigurableCreated($id_product)
    {
        return in_array($id_product, $this->configurable_ids);
    }

    public function generate()
    {

        $start = microtime(true);
        $memory = memory_get_usage();

        $products = $this->productService->getProducts($this->shop, $this->language, 0, 100000);
        $total = count($products);
        $iter = 1;
        $barWidth = 40;
        foreach ($products as $product) {

            // progress
            $progress = $iter / $total;
            $progressWidth = (int) ($progress * $barWidth);
            $progressBar = str_repeat('█', $progressWidth) . str_repeat(' ', $barWidth - $progressWidth);
            if (php_sapi_name() === "cli")
                echo "\033[K";

            $item = new GoogleShoppingProduct();
            $feedProduct = ProductBuilderFactory::create(
                $product,
                $item,
                Config::get("_ACCELASEARCH_SYNCTYPE")
            );
            $feedProduct->build($this->shop, $this->language);
            $this->feed->addProduct($feedProduct->getItem());

            if (php_sapi_name() === "cli")
                echo "Progress: [$progressBar] " . round($progress * 100, 2) . "%\r";
            $iter++;
        }

        if (php_sapi_name() === "cli")
            echo "\n\n";

        $end = microtime(true);
        $memory = memory_get_usage() - $memory;

        $this->execution_time = ($end - $start);
        $this->memory_used = ($memory / 1024 / 1024);

        if ($this->debug) {
            echo "Time: " . ($end - $start) . "\n";
            echo "Memory: " . ($memory / 1024 / 1024) . " MB\n";
        }

        echo "Products: " . count($products) . "\n";

        try {
            $this->filesystem->dumpFile(
                $this->getOutputPath(),
                $this->feed->build()
            );
        } catch (IOExceptionInterface $exception) {
            $message = "An error occurred while creating your feed at " . $exception->getPath() . "\n" . $exception->getMessage() . "\n";
            Log::write($message, Log::ERROR, Log::CONTEXT_PRODUCT_FEED_CREATION);
            echo $message;
        }

        echo "Feed generated at " . $this->getOutputPath() . "\n";
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
}
<?php

namespace Accelasearch\Accelasearch\Command;

use Accelasearch\Accelasearch\Builder\ProductBuilder;
use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Entity\Shop;
use Accelasearch\Accelasearch\Factory\ContextFactory;
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

    public function createConfigurable($product)
    {
        $this->configurable_ids[] = $product["id_product"];
        $product["id_product_attribute"] = $product["id_product"];
        $product["id_attribute"] = 0;
        $item = new GoogleShoppingProduct();
        $feedProduct = new ProductBuilder($product, $item);
        $feedProduct->build($this->shop, $this->language);
        $this->feed->addProduct($feedProduct->getItem());
    }

    public function generate()
    {

        $start = microtime(true);
        $memory = memory_get_usage();

        $products = $this->productService->getProducts($this->shop, $this->language, 0, 100000);

        foreach ($products as $product) {
            $item = new GoogleShoppingProduct();
            $feedProduct = new ProductBuilder($product, $item);
            $feedProduct->build($this->shop, $this->language);
            $this->feed->addProduct($feedProduct->getItem());
            if ($feedProduct->hasVariants() && !$this->isConfigurableCreated($product["id_product"])) {
                $this->createConfigurable($product);
            }
        }

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
            echo "An error occurred while creating your feed at " . $exception->getPath() . "\n" . $exception->getMessage() . "\n";
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
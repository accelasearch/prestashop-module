<?php

namespace Accelasearch\Accelasearch\Command;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Entity\AsShop;
use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Entity\Shop;
use Accelasearch\Accelasearch\Factory\ContextFactory;
use Accelasearch\Accelasearch\Factory\ProductBuilderFactory;
use Accelasearch\Accelasearch\Logger\Log;
use Accelasearch\Accelasearch\Logger\RemoteLog;
use Accelasearch\Accelasearch\Service\ServiceInterface;
use Vitalybaev\GoogleMerchant\Product as GoogleShoppingProduct;
use Vitalybaev\GoogleMerchant\Feed as GoogleShoppingFeed;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\ProgressIndicator;
use XMLReader;
use XMLWriter;

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
    private $filesystem;
    private $configurable_ids = [];
    public const FACTOR = 10000;
    public function __construct(Shop $shop, Language $language, ServiceInterface $productService)
    {
        $this->shop = $shop;
        $this->language = $language;
        $this->productService = $productService;
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

        if(php_sapi_name() === "cli") {
            $progressIndicator = new ProgressIndicator($output);
            $progressIndicator->start("Getting products from Database");
        }

        $totalProducts = $this->productService->getProductsNb($this->shop->getId(), $this->language->getId());
        $totalProcessed = 0;
        $iteration_number = 0;

        Log::write("Getting $totalProducts products info from Database", Log::INFO, Log::CONTEXT_PRODUCT_FEED_CREATION);

        do {
            $feed = new GoogleShoppingFeed(
                $this->shop->ps->name . " - " . $this->language->ps->name,
                ContextFactory::getContext()->link->getBaseLink($this->shop->getId()),
                "Google Shopping Feed for " . $this->shop->ps->name . " - " . $this->language->ps->name
            );

            $products = $this->productService->getProducts($this->shop, $this->language, $iteration_number * self::FACTOR, self::FACTOR, $progressIndicator);
            $totalProcessed += count($products);
            $iteration_number++;
            $totalProducts -= self::FACTOR;
            if(php_sapi_name() === "cli") {
                $progressIndicator->finish(count($products) . " Products retrieved");
                echo "\n\n";
            }

            Log::write(count($products) . " Products retrieved - " . $iteration_number . " iteration", Log::INFO, Log::CONTEXT_PRODUCT_FEED_CREATION);

            if(php_sapi_name() === "cli") {
                $progressBar = new ProgressBar($output, count($products));
                $progressBar->start();
            }

            foreach($products as $product) {

                $item = new GoogleShoppingProduct();
                $feedProduct = ProductBuilderFactory::create(
                    $product,
                    $item,
                    Config::get("_ACCELASEARCH_SYNCTYPE")
                );
                $feedProduct->build($this->shop, $this->language);
                $feed->addProduct($feedProduct->getItem());

                if(php_sapi_name() === "cli") {
                    $progressBar->advance();
                }
            }

            try {
                $this->filesystem->dumpFile(
                    $this->getOutputPath($iteration_number),
                    $feed->build()
                );
            } catch (IOExceptionInterface $exception) {
                $message = "An error occurred while creating your feed at " . $exception->getPath() . "\n" . $exception->getMessage() . "\n";
                RemoteLog::write($message, Log::ERROR, Log::CONTEXT_PRODUCT_FEED_CREATION);
                echo $message;
            }

        } while($totalProducts > 0);

        if(php_sapi_name() === "cli") {
            $progressBar->finish();
            echo "\n\n";
        }

        $end = microtime(true);
        $memory = memory_get_usage(true) - $memory;

        $this->execution_time = ($end - $start);
        $this->memory_used = ($memory / 1024 / 1024);

        if($this->debug) {
            echo "Time: " . ($end - $start) . "\n";
            echo "Memory: " . ($memory / 1024 / 1024) . " MB\n";
        }

        echo "Products: " . $totalProcessed . "\n";

        // create an array of filenames by iterations number
        $filePaths = [];
        for($i = 0; $i < $iteration_number; $i++) {
            $filePaths[] = $this->getOutputPath($i + 1);
        }

        $this->mergeXmlFiles($filePaths, $this->getOutputPath());

        // remove all files except the merged one
        foreach($filePaths as $filePath) {
            if($filePath !== $this->getOutputPath()) {
                $this->filesystem->remove($filePath);
            }
        }

        AsShop::updateFeedUrlByIdShopAndIdLang($this->shop->getId(), $this->language->getId(), $this->getFeedUrl());

        Log::write("Feed generated in " . $this->execution_time . ", memory used: " . $this->memory_used, Log::INFO, Log::CONTEXT_PRODUCT_FEED_CREATION);
        echo "Feed generated at " . $this->getOutputPath() . "\n\n";
        return $this->getFeedUrl();
    }


    private function mergeXmlFiles(array $filePaths, $outputFile)
    {
        $output = new XMLWriter();
        $output->openURI($outputFile);
        $output->startDocument('1.0');
        $output->setIndent(true);

        $output->startElement('rss');
        $output->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $output->startElement('channel');

        $firstFile = true;

        foreach ($filePaths as $filePath) {
            $reader = new XMLReader();
            if (!$reader->open($filePath)) {
                continue;
            }

            while ($reader->read()) {
                if ($reader->nodeType == XMLReader::ELEMENT) {
                    if ($reader->localName == 'item') {
                        $output->startElement('item');
                        while ($reader->read()) {
                            if ($reader->nodeType == XMLReader::ELEMENT) {
                                $name = $reader->localName;
                                $output->startElement("g:$name");
                                if (!$reader->isEmptyElement) {
                                    $reader->read();
                                    if ($reader->nodeType == XMLReader::TEXT) {
                                        $output->text($reader->value);
                                    } elseif ($reader->nodeType == XMLReader::CDATA) {
                                        $output->writeCData($reader->value);
                                    }
                                }
                                $output->endElement();
                            } elseif ($reader->nodeType == XMLReader::END_ELEMENT && $reader->localName == 'item') {
                                break;
                            }
                        }
                        $output->endElement();
                    } elseif ($firstFile && in_array($reader->localName, ['title', 'link', 'description'])) {
                        $output->startElement($reader->localName);
                        if (!$reader->isEmptyElement) {
                            $reader->read();
                            if ($reader->nodeType == XMLReader::TEXT) {
                                $output->text($reader->value);
                            } elseif ($reader->nodeType == XMLReader::CDATA) {
                                $output->writeCData($reader->value);
                            }
                        }
                        $output->endElement();
                    }
                }
            }

            $reader->close();
            $firstFile = false;
        }

        $output->endElement();
        $output->endElement();
        $output->endDocument();

        $output->flush();
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

    public function getOutputPath($iteration = null)
    {
        $suffix = $iteration ? "_" . $iteration : "";
        return _PS_MODULE_DIR_ . 'accelasearch/' . Config::FEED_OUTPUT_PATH . Config::get("_ACCELASEARCH_FEED_RANDOM_TOKEN") . "-" . $this->shop->getId() . '_' . $this->language->getId() . $suffix . '.xml';
    }

    public function getFeedUrl()
    {
        return $this->shop->getUrl($this->language->getId()) . 'modules/accelasearch/' . Config::FEED_OUTPUT_PATH . Config::get("_ACCELASEARCH_FEED_RANDOM_TOKEN") . "-" . $this->shop->getId() . '_' . $this->language->getId() . '.xml';
    }
}

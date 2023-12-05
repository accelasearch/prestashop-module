<?php

namespace Accelasearch\Accelasearch\Service;

use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Entity\Shop;

class AllConfigurableWithSimpleService extends AbstractService implements ServiceInterface
{
    private $configurable_ids = [];
    /**
     * Returns an array of products for the given shop, language, start and limit.
     *
     * @param Shop $shop The shop object.
     * @param Language $language The language object.
     * @param int $start The start index.
     * @param int $limit The limit of products to return.
     * @return array The array of products.
     */
    public function getProducts(Shop $shop, Language $language, int $start, int $limit, $progressIndicator = null): array
    {
        $products = $this->productRepository->getDbProducts($start, $limit, $language->getId(), $shop->ps);
        $products = $this->productDecorator->decorateProducts($products);
        $this->addConfigurables($products);
        return $products;
    }

    private function addConfigurables(&$products)
    {
        foreach ($products as $product) {
            if (!$this->isConfigurableCreated($product["id_product"]) && !empty($product["id_attribute"])) {
                $products[] = $this->createConfigurable($product, $products);
            }
        }
    }

    private function getLowestPricesFromProducts($id_product, $products)
    {
        $basePrices = [];
        $salePrices = [];
        foreach ($products as $product) {
            if ($product["id_product"] == $id_product) {
                $basePrices[] = $product["price_tax_incl"];
                $salePrices[] = $product["sale_price_tax_incl"];
            }
        }
        return [min($basePrices), min($salePrices)];
    }

    public function createConfigurable($product, $products)
    {
        $this->configurable_ids[] = $product["id_product"];
        $product["id_product_attribute"] = $product["id_product"];
        $product["id_attribute"] = 0;
        $lowestPrices = $this->getLowestPricesFromProducts($product["id_product"], $products);
        $product["price_tax_incl"] = $lowestPrices[0];
        $product["sale_price_tax_incl"] = $lowestPrices[1];
        return $product;
    }

    public function isConfigurableCreated($id_product)
    {
        return in_array($id_product, $this->configurable_ids);
    }
}
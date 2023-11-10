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
        foreach ($products as $product) {
            if (!$this->isConfigurableCreated($product["id_product"])) {
                $products[] = $this->createConfigurable($product);
            }
            if (php_sapi_name() === "cli")
                $progressIndicator->advance();
        }
        return $products;
    }

    public function createConfigurable($product)
    {
        $this->configurable_ids[] = $product["id_product"];
        $product["id_product_attribute"] = $product["id_product"];
        $product["id_attribute"] = 0;
        return $product;
    }

    public function isConfigurableCreated($id_product)
    {
        return in_array($id_product, $this->configurable_ids);
    }
}
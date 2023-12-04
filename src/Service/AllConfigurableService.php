<?php

namespace Accelasearch\Accelasearch\Service;

use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Entity\Shop;

class AllConfigurableService extends AbstractService implements ServiceInterface
{
    /**
     * Get an array of products with their configurable options, SKUs, features, and attributes.
     *
     * @param Shop $shop The shop object.
     * @param Language $language The language object.
     * @param int $start The start index of the products to retrieve.
     * @param int $limit The maximum number of products to retrieve.
     *
     * @return array An array of products with their configurable options, SKUs, features, and attributes.
     */
    public function getProducts(Shop $shop, Language $language, int $start, int $limit, $progressIndicator = null): array
    {
        $products = $this->productRepository->getDbProducts($start, $limit, $language->getId(), $shop->ps);
        $products = $this->productDecorator->decorateProducts($products);

        if (php_sapi_name() === "cli")
            $progressIndicator->advance();

        // group same id_product under same array
        $products = array_reduce($products, function ($carry, $item) {
            $carry[$item["id_product"]]["configurable"] = $item;
            if (!isset($carry[$item["id_product"]]["sku"]))
                $carry[$item["id_product"]]["sku"] = [];
            // prevent duplicates
            if (!in_array($item["reference"], $carry[$item["id_product"]]["sku"]))
                $carry[$item["id_product"]]["sku"][] = $item["reference"];
            $carry[$item["id_product"]]["features"] = $this->getItemFeatures($item);
            foreach ($this->getItemAttributes($item) as $attributeName => $attributeValue) {
                // skip if exists
                if (isset($carry[$item["id_product"]]["attributes"][$attributeName]) && in_array($attributeValue, $carry[$item["id_product"]]["attributes"][$attributeName]))
                    continue;
                $carry[$item["id_product"]]["attributes"][$attributeName][] = $attributeValue;
            }
            return $carry;
        }, []);

        return $products;
    }

    public function getItemEntityArray($item, $entity)
    {
        if (empty($item[$entity]))
            return [];
        $entities = [];
        foreach ($item[$entity] as $name => $entity) {
            $entities[$name] = $entity;
        }
        return $entities;
    }

    public function getItemAttributes($item)
    {
        return $this->getItemEntityArray($item, "attributes");
    }

    public function getItemFeatures($item)
    {
        return $this->getItemEntityArray($item, "features");
    }

}
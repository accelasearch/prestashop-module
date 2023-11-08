<?php

namespace Accelasearch\Accelasearch\Service;

use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Entity\Shop;

class AllSimpleService extends AbstractService implements ServiceInterface
{
    public function getProducts(Shop $shop, Language $language, int $start, int $limit): array
    {
        $products = $this->productRepository->getDbProducts($start, $limit, $language->getId(), $shop->ps);
        $products = $this->productDecorator->decorateProducts($products);
        return $products;
    }
}
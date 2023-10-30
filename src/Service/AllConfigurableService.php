<?php

namespace Accelasearch\Accelasearch\Service;

use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Entity\Shop;

class AllConfigurableService extends AbstractService implements ServiceInterface
{
    public function getProducts(Shop $shop, Language $language, int $start, int $limit): array
    {
        $products = $this->productRepository->getProducts($shop, $language, $start, $limit);
        $products = $this->productDecorator->decorateProducts($products);
        return $products;
    }
}
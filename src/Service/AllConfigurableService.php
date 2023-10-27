<?php

namespace Accelasearch\Accelasearch\Service;

class AllConfigurableService extends AbstractService implements ServiceInterface
{
    public function getProducts(int $id_lang, int $start, int $limit): array
    {
        $products = $this->productRepository->getProducts($id_lang, $start, $limit);
        $products = $this->productDecorator->decorateProducts($products);
        return $products;
    }
}
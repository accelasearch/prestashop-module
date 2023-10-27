<?php

namespace Accelasearch\Accelasearch\Provider;

use Accelasearch\Accelasearch\Repository\ProductRepository;

class ProductDataProvider
{

    private $productRepository;
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getFormattedData()
    {
        return $this->productRepository->getProducts(1, 0, 1000);
    }
}
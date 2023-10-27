<?php

namespace Accelasearch\Accelasearch\Decorator;

use Accelasearch\Accelasearch\Factory\ContextFactory;

class ProductDecorator
{
    private $productDataProvider;
    public function __construct(\Accelasearch\Accelasearch\Provider\ProductDataProvider $productDataProvider)
    {
        $this->productDataProvider = $productDataProvider;
    }

    public function decorateProducts(array $products): array
    {
        foreach ($products as $key => $product) {
            $products[$key]['link'] = ContextFactory::getContext()->link->getProductLink($product['id_product']);
        }
        return $products;
    }
}
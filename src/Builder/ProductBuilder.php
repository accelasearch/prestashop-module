<?php

namespace Accelasearch\Accelasearch\Builder;

use Vitalybaev\GoogleMerchant\Product as GoogleShoppingProduct;

class ProductBuilder
{
    private $product;
    private $item;
    public function __construct(
        $product,
        GoogleShoppingProduct $item
    ) {
        $this->product = $product;
        $this->item = $item;
    }

    public function build()
    {
        $this->item->setId($this->product['id_product']);
        $this->item->setTitle($this->product['name']);
        $this->item->setLink($this->product['link']);
    }

    public function getItem()
    {
        return $this->item;
    }
}
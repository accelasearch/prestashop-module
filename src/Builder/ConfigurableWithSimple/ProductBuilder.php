<?php

namespace Accelasearch\Accelasearch\Builder\ConfigurableWithSimple;

use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Entity\Shop;
use Accelasearch\Accelasearch\Builder\ProductBuilderAbstract;
use Vitalybaev\GoogleMerchant\Product as GoogleShoppingProduct;

class ProductBuilder extends ProductBuilderAbstract
{
    private $product;
    private $item;
    public function __construct(
        $product,
        GoogleShoppingProduct $item
    ) {
        parent::__construct($product, $item);
        $this->product = $product;
        $this->item = $item;
    }

    public function build(Shop $shop, Language $language)
    {
        parent::build($shop, $language);
        if ((int) $this->product["id_attribute"]) {
            $this->item->setAttribute("item_group_id", $this->product['id_product']);
        }
    }

}
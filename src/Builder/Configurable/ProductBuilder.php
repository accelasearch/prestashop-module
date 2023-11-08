<?php

namespace Accelasearch\Accelasearch\Builder\Configurable;

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
        $product["configurable"]["attributes"] = $product["attributes"] ?? [];
        $product["configurable"]["features"] = $product["features"] ?? [];
        $product["configurable"]["sku"] = $product["sku"] ?? [];
        $product["configurable"]["id_product_attribute"] = $product["configurable"]["id_product"];
        $product["configurable"]["id_attribute"] = 0;
        parent::__construct($product["configurable"], $item);
        $this->product = $product;
        $this->item = $item;
    }
    public function build(Shop $shop, Language $language)
    {
        parent::build($shop, $language);

        // add variants and sku as attributes
        if (!empty($this->product["sku"])) {
            foreach ($this->product["sku"] as $sku) {
                $this->item->addAttribute("sku", $sku);
            }
        }

        if (!empty($this->product["attributes"])) {
            foreach ($this->product["attributes"] as $attribute_name => $attribute_value) {
                $attribute_name = preg_replace("/[^A-Za-z0-9]/", "", $attribute_name);
                if (!is_array($attribute_value)) {
                    $attribute_value = [$attribute_value];
                }
                foreach ($attribute_value as $value) {
                    $this->item->addAttribute($attribute_name, $value);
                }
            }
        }
    }

    public function getItem()
    {
        return $this->item;
    }

}
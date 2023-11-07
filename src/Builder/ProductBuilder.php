<?php

namespace Accelasearch\Accelasearch\Builder;

use Accelasearch\Accelasearch\Config\Config;
use Vitalybaev\GoogleMerchant\Product as GoogleShoppingProduct;
use Vitalybaev\GoogleMerchant\Product\Availability\Availability;

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

    public function hasVariants()
    {
        return (int) $this->product["id_attribute"];
    }
    public function build()
    {

        $colorLabel = Config::getColorLabel();
        $sizeLabel = Config::getSizeLabel();

        // basic product information
        $this->item->setId($this->product['id_product_attribute']);
        $this->item->setTitle($this->product['name']);
        $this->item->setDescription($this->product['description']);
        $this->item->setLink($this->product['link']);
        $this->item->setImage($this->product['cover']);
        $this->item->setBrand($this->product['manufacturer']);
        $this->item->setGtin($this->product['ean']);

        // set color if exists and id_attribute is not 0
        if (isset($this->product["attributes"][$colorLabel]) && (int) $this->product["id_attribute"])
            $this->item->setColor($this->product["attributes"][$colorLabel]);

        // set size if exists
        if (isset($this->product["attributes"][$sizeLabel]) && (int) $this->product["id_attribute"])
            $this->item->setSize($this->product["attributes"][$sizeLabel]);

        // custom product attributes
        if (!empty($this->product["features"])) {
            foreach ($this->product["features"] as $feature_name => $feature_value) {
                $feature_name = preg_replace("/[^A-Za-z0-9]/", "", $feature_name);
                $this->item->setAttribute($feature_name, $feature_value);
            }
        }

        // availability
        if ((int) $this->product["quantity"] > 0) {
            $this->item->setAvailability(Availability::IN_STOCK);
        } else {
            $this->item->setAvailability(Availability::OUT_OF_STOCK);
        }

        // price
        $currency = \Context::getContext()->currency->iso_code;
        $this->item->setPrice($this->product['price_tax_incl'] . " " . $currency);
        $this->item->setSalePrice($this->product['sale_price_tax_incl'] . " " . $currency);

        // categories
        $this->item->setProductType($this->product['category_path']);

        // if has variants
        if ((int) $this->product["id_attribute"]) {
            $this->item->setAttribute("item_group_id", $this->product['id_product']);
        }

    }

    public function getItem()
    {
        return $this->item;
    }
}
<?php

namespace AccelaSearch\Updater;

class UpdateContext
{

  public $id_shop;
  public $id_lang;
  public $as_shop_id;
  public $as_shop_real_id;
  public $id_product;
  public $id_product_attribute;

  public function __construct($id_shop, $id_lang, $as_shop_id, $as_shop_real_id, $id_product = 0, $id_product_attribute = 0)
  {
    $this->id_shop = $id_shop;
    $this->id_lang = $id_lang;
    $this->as_shop_id = $as_shop_id;
    $this->as_shop_real_id = $as_shop_real_id;
    $this->id_product = $id_product;
    $this->id_product_attribute = $id_product_attribute;
  }

  public function setIdProductAttribute(int $id_product_attribute)
  {
    $this->id_product_attribute = $id_product_attribute;
  }

  public function isGlobalOperation(): bool
  {
    return (int)$this->id_product === 0;
  }

  public function buildExternalId(array $append): string
  {
    $append = implode("_", $append);
    return $this->id_shop."_".$this->id_lang."_".$append;
  }

}

 ?>

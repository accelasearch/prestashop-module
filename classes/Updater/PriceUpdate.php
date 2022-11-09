<?php

namespace AccelaSearch\Updater;
use AccelaSearch\Query;

class PriceUpdate extends UpdateOperation implements Operation
{

  private $queries = "";

  public function __construct()
  {
    $this->setName("price");
  }

  public function generateQueries(UpdateRow $update_row, UpdateContext $context)
  {

    $id_product = $context->id_product;
    $id_product_attribute = $context->id_product_attribute;

    if($context->isGlobalOperation()){
      $this->queries .= Query::getGlobalProductPriceUpdateQuery($context->id_shop, $context->id_lang, $context->as_shop_id);
      $update_row->unsetOperationIfExist("i");
      $update_row->unsetOperationIfExist("d");
      $update_row->unsetOperationIfExist("u");
    }

    if($update_row->isInsertOperation()){
      [
        "id_product" => $row_id_product,
        "id_product_attribute" => $row_id_product_attribute
      ] = $update_row->getRow()["i"]["id_product"]["raw"];

      $this->queries .= Query::getProductPriceUpdateQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang);
    }

    if($update_row->isDeleteOperation()){
      [
        "id_product" => $row_id_product,
        "id_product_attribute" => $row_id_product_attribute
      ] = $update_row->getRow()["d"]["id_specific_price"]["raw"];

      $this->queries .= Query::getProductPriceUpdateQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang);
    }

    if($update_row->isUpdateOperation()){
      [
        "id_product" => $row_id_product,
        "id_product_attribute" => $row_id_product_attribute
      ] = $update_row->getRow()["u"]["id_product"]["raw"];

      $this->queries .= Query::getProductPriceUpdateQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang);
    }

    return $this;

  }

  public function getQueries(): string
  {
    return $this->queries;
  }

}

 ?>

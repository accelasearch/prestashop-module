<?php

namespace AccelaSearch\Updater;

use AccelaSearch\Query;

class StockUpdate extends UpdateOperation implements Operation
{

  private $queries = "";

  public function __construct()
  {
    $this->setName("stock");
  }

  public function generateQueries(UpdateRow $update_row, UpdateContext $context)
  {

    $id_product = $context->id_product;
    $id_product_attribute = $context->id_product_attribute;

    if ($update_row->isUpdateOperation()) {
      [
        "id_product" => $row_id_product,
        "id_product_attribute" => $row_id_product_attribute,
        "value" => $quantity
      ] = $update_row->getRow()["u"]["quantity"]["raw"];

      $this->queries .= Query::getProductStockUpdateQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang, $quantity);
    }

    return $this;
  }

  public function getQueries(): string
  {
    return $this->queries;
  }
}

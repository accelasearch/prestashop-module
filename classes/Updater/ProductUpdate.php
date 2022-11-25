<?php

namespace AccelaSearch\Updater;

use AccelaSearch\Query;

class ProductUpdate extends UpdateOperation implements Operation
{

  private $queries = "";

  public function __construct()
  {
    $this->setName("product");
  }

  public function generateQueries(UpdateRow $update_row, UpdateContext $context)
  {

    $id_product = $context->id_product;
    $id_product_attribute = $context->id_product_attribute;

    if ($update_row->isDeleteOperation()) {
      $this->queries .= Query::getByName("remote_product_delete", [
        "product_external_id_str" => $context->buildExternalId([$id_product, 0])
      ]);
    }

    if ($update_row->isInsertOperation()) {
      $update_row->unsetOperationIfExist("u");
      $update_row->removeFromStack("image");
      $update_row->removeFromStack("stock");
      $update_row->removeFromStack("price");
      $update_row->removeFromStack("category_association");
      $update_row->removeFromStack("attribute_image");
      $update_row->removeFromStack("variant");
      $this->queries .= Query::getProductCreationQuery($id_product, $context->id_shop, $context->id_lang, $context->as_shop_id, $context->as_shop_real_id);
    }

    if ($update_row->isUpdateOperation()) {
      foreach ($update_row->getRow()["u"] as $entity => $update) {
        $this->queries .= Query::getProductUpdateQueryByEntity($update["raw"], $context->id_shop, $context->id_lang);
      }
    }

    return $this;
  }

  public function getQueries(): string
  {
    return $this->queries;
  }
}

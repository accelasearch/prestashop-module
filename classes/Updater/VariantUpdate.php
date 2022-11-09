<?php

namespace AccelaSearch\Updater;
use AccelaSearch\Query;

class VariantUpdate extends UpdateOperation implements Operation
{

  private $queries = "";

  public function __construct()
  {
    $this->setName("variant_update");
  }

  public function generateQueries(UpdateRow $update_row, UpdateContext $context)
  {

    $id_product = $context->id_product;
    $id_product_attribute = $context->id_product_attribute;

    if($update_row->isInsertOperation()){
      $this->queries .= Query::transformProductAndCreateVariant($id_product, $id_product_attribute, $context->id_shop, $context->id_lang, $context->as_shop_id);
    }

    if($update_row->isDeleteOperation()){
      $externalidstr = $context->buildExternalId([$id_product, $id_product_attribute]);
      $this->queries .= "UPDATE products SET deleted = 1 WHERE externalidstr = '$externalidstr';";
    }

    return $this;

  }

  public function getQueries(): string
  {
    return $this->queries;
  }

}

 ?>

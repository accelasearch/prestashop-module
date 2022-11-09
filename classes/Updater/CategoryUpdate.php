<?php

namespace AccelaSearch\Updater;
use AccelaSearch\Query;

class CategoryUpdate extends UpdateOperation implements Operation
{

  private $queries = "";

  public function __construct()
  {
    $this->setName("category");
  }

  public function generateQueries(UpdateRow $update_row, UpdateContext $context)
  {

    $id_product = $context->id_product;
    $id_product_attribute = $context->id_product_attribute;

    if($update_row->isDeleteOperation()){
      $this->queries .= Query::getCategoryDeleteQuery($id_product, $context->id_shop, $context->id_lang, $context->as_shop_id);
      $update_row->unsetOperationIfExist("i");
      $update_row->unsetOperationIfExist("u");
    }

    if($update_row->isInsertOperation()){
      $update_row->unsetOperationIfExist("u");
      $this->queries .= Query::getCategoryCreationQuery($id_product, $context->id_shop, $context->id_lang, $context->as_shop_id);
    }

    if($update_row->isUpdateOperation()){
      $op_name = array_keys($update_row->getRow()["u"])[0];
      $new_value = $update_row->getRow()["u"][$op_name]["value"];
      $this->queries .= Query::getCategoryUpdateQuery($id_product, $new_value, $context->id_shop, $context->id_lang, $context->as_shop_id, $op_name);
    }

    return $this;

  }

  public function getQueries(): string
  {
    return $this->queries;
  }

}

 ?>

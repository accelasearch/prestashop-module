<?php

namespace AccelaSearch\Updater;

class CategoryProductUpdate extends UpdateOperation implements Operation
{

  private $queries = "";

  public function __construct()
  {
    $this->setName("category_product");
  }

  public function generateQueries(UpdateRow $update_row, UpdateContext $context)
  {

    $id_product = $context->id_product;
    $id_product_attribute = $context->id_product_attribute;

    if($update_row->isDeleteOperation()){
      foreach($update_row->getRow()["d"] as $id_category_str => $cat_update){
        [
          "value" => $id_category,
          "id_product" => $id_product
        ] = $cat_update["raw"];
        $lastupdate = date("Y-m-d H:i:s");
        $externalidstr = $context->buildExternalId([$id_category]);
        $ext_product_idstr = $context->buildExternalId([$id_product, 0]);
        $this->queries .= "UPDATE products_categories SET deleted = 1, lastupdate = '$lastupdate' WHERE productid = (SELECT id FROM products WHERE externalidstr = '$ext_product_idstr') AND categoryid = (SELECT id FROM categories WHERE externalidstr = '$externalidstr');";
      }
    }

    if($update_row->isInsertOperation()){
      foreach($update_row->getRow()["i"] as $id_category_str => $cat_update){
        [
          "value" => $id_category,
          "id_product" => $id_product
        ] = $cat_update["raw"];
        $lastupdate = date("Y-m-d H:i:s");
        $externalidstr = $context->buildExternalId([$id_category]);
        $ext_product_idstr = $context->buildExternalId([$id_product, 0]);
        $id_association = \AS_Collector::getInstance()->getValue("SELECT id FROM products_categories WHERE productid = (SELECT id FROM products WHERE externalidstr = '$ext_product_idstr') AND categoryid = (SELECT id FROM categories WHERE externalidstr = '$externalidstr')");
        if(!$id_association){
          $this->queries .= "INSERT INTO products_categories (categoryid, productid) VALUES ((SELECT id FROM categories WHERE externalidstr = '$externalidstr'),(SELECT id FROM products WHERE externalidstr = '$ext_product_idstr'));";
        }else{
          $this->queries .= "UPDATE products_categories SET deleted = 0, lastupdate = '$lastupdate' WHERE id = $id_association;";
        }
      }
    }

    return $this;

  }

  public function getQueries(): string
  {
    return $this->queries;
  }

}

 ?>

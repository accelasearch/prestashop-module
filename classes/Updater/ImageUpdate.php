<?php

namespace AccelaSearch\Updater;

use AccelaSearch\Query;

class ImageUpdate extends UpdateOperation implements Operation
{

  private $queries = "";

  public function __construct()
  {
    $this->setName("image");
  }

  public function generateQueries(UpdateRow $update_row, UpdateContext $context)
  {

    $id_product = $context->id_product;
    $id_product_attribute = $context->id_product_attribute;

    if ($update_row->isDeleteOperation()) {
      foreach ($update_row->getRow()["d"] as $id_image_str => $im_update) {
        [
          "id_product" => $row_id_product,
          "id_product_attribute" => $row_id_product_attribute,
          "value" => $id_image
        ] = $im_update["raw"];
        $image_external_id_cover = $context->buildExternalId([
          $row_id_product,
          $row_id_product_attribute,
          $id_image,
          "cover"
        ]);
        $image_external_id_others = $context->buildExternalId([
          $row_id_product,
          $row_id_product_attribute,
          $id_image,
          "others"
        ]);
        $this->queries .= "UPDATE products_images SET deleted = 1 WHERE externalidstr = '$image_external_id_cover';";
        $this->queries .= "UPDATE products_images SET deleted = 1 WHERE externalidstr = '$image_external_id_others';";
      }
    }

    if ($update_row->isInsertOperation()) {
      foreach ($update_row->getRow()["i"] as $id_image_str => $im_update) {
        [
          "id_product" => $row_id_product,
          "id_product_attribute" => $row_id_product_attribute,
          "value" => $id_image
        ] = $im_update["raw"];

        $this->queries .= Query::getProductImageByIdQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang, $id_image);
      }
    }

    if ($update_row->isUpdateOperation()) {
      foreach ($update_row->getRow()["u"] as $id_image_str => $im_update) {
        [
          "id_product" => $row_id_product,
          "id_product_attribute" => $row_id_product_attribute,
          "value" => $id_image
        ] = $im_update["raw"];
        $this->queries .= Query::getProductImageByIdQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang, $id_image);
      }
    }

    return $this;
  }

  public function getQueries(): string
  {
    return $this->queries;
  }
}

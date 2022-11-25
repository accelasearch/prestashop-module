<?php

namespace AccelaSearch\Updater;

class Updater
{

  private $context;
  private $update_stack = [];

  public function __construct(UpdateContext $context)
  {
    $this->context = $context;
  }

  public function populateUpdateStack(UpdateRow $update_row)
  {
    $types = $update_row->getTypes();
    $to_remove = $update_row->getOperationsToRemoveFromStack();
    $types = array_diff($types, $to_remove);
    if (in_array("product", $types)) $this->addToStack(new ProductUpdate);
    if (in_array("image", $types)) $this->addToStack(new ImageUpdate);
    if (in_array("stock", $types)) $this->addToStack(new StockUpdate);
    if (in_array("price", $types)) $this->addToStack(new PriceUpdate);
    if (in_array("category", $types)) $this->addToStack(new CategoryUpdate);
    if (in_array("category_product", $types)) $this->addToStack(new CategoryProductUpdate);
    if (in_array("attribute_image", $types)) $this->addToStack(new AttributeImageUpdate);
    if (in_array("variant", $types)) $this->addToStack(new VariantUpdate);
  }

  public function getQueries(UpdateRow $update_row): string
  {
    $this->populateUpdateStack($update_row);
    $queries = "";
    foreach ($this->update_stack as $op => $update) {
      $update_row->setEntity($op);
      $queries .= $update->generateQueries($update_row, $this->context)->getQueries();
    }
    if (!$this->context->isGlobalOperation() && !empty($queries)) {
      $externalidstr = $this->context->buildExternalId([$this->context->id_product, ""]);
      $timestamp = date("Y-m-d H:i:s");
      $queries .= "UPDATE products SET lastupdate = '$timestamp' WHERE externalidstr LIKE '$externalidstr%';";
    }
    return $queries;
  }

  public function setContext(UpdateContext $context)
  {
    $this->context = $context;
  }

  public function getContext()
  {
    return $this->context;
  }

  public function addToStack(UpdateOperation $operation)
  {
    $this->update_stack[$operation->getName()] = $operation;
  }

  public function removeFromStack(UpdateOperation $operation)
  {
    unset($this->update_stack[$operation->getName()]);
  }
}

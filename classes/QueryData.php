<?php

namespace AccelaSearch;

class QueryData
{
  public $as_attributes_ids;
  public $as_product_types;
  public $as_categories;
  public $warehouse_id;
  public $customer_groups;
  public $users_groups;
  public $link;
  public $currencies_cart;
  public $as_instance;

  public function __construct($data)
  {
    foreach ($data as $private_var_name => $private_var_value) {
      $this->{$private_var_name} = $private_var_value;
    }
  }
}

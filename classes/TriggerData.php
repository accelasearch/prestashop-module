<?php

namespace AccelaSearch;

class TriggerData
{
  public $when;
  public $type;
  public $table;
  public $fields = [];
  public function __construct($data)
  {
    foreach($data as $private_var_name => $private_var_value){
      $this->{$private_var_name} = $private_var_value;
    }
  }
}

?>

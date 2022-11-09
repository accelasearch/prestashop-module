<?php

namespace AccelaSearch\Updater;

abstract class UpdateOperation
{

  private $name;

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getName()
  {
    return $this->name;
  }

}

 ?>

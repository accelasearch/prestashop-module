<?php

namespace AccelaSearch\Updater;

interface RowOperations
{
  public function isDeleteOperation(): bool;
  public function isInsertOperation(): bool;
  public function isUpdateOperation(): bool;
}

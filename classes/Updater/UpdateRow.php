<?php

namespace AccelaSearch\Updater;

class UpdateRow implements RowOperations
{

  private $row;
  private $entity;
  private $to_remove = [];

  public function __construct(array $row)
  {
    $this->row = $row;
  }

  public function getTypes(): array
  {
    return array_keys($this->row);
  }

  public function setEntity(string $entity)
  {
    $this->entity = $entity;
  }

  public function getEntity(): string
  {
    return $this->entity;
  }

  public function getRow()
  {
    return $this->row[$this->getEntity()];
  }

  public function setRow(array $row)
  {
    $this->row = $row;
  }

  public function isDeleteOperation(): bool
  {
    return isset($this->row[$this->getEntity()]["d"]);
  }

  public function isInsertOperation(): bool
  {
    return isset($this->row[$this->getEntity()]["i"]);
  }

  public function isUpdateOperation(): bool
  {
    return isset($this->row[$this->getEntity()]["u"]);
  }

  public function unsetOperationIfExist(string $op)
  {
    if (isset($this->row[$op])) unset($this->row[$op]);
  }

  public function removeFromStack(string $op)
  {
    $this->to_remove[] = $op;
  }

  public function getOperationsToRemoveFromStack(): array
  {
    return $this->to_remove;
  }
}

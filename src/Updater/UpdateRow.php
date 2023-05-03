<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace AccelaSearch\Updater;

class UpdateRow implements RowOperationsInterface
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
        return isset($this->row[$this->getEntity()]['d']);
    }

    public function isInsertOperation(): bool
    {
        return isset($this->row[$this->getEntity()]['i']);
    }

    public function isUpdateOperation(): bool
    {
        return isset($this->row[$this->getEntity()]['u']);
    }

    public function unsetOperationIfExist(string $op)
    {
        if (isset($this->row[$op])) {
            unset($this->row[$op]);
        }
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

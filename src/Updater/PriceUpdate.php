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

use AccelaSearch\Query\Query;
use AccelaSearch\Queue;

class PriceUpdate extends UpdateOperationAbstract implements OperationInterface
{
    private $queries = '';

    public function __construct()
    {
        $this->setName('price');
    }

    public function generateQueries(UpdateRow $update_row, UpdateContext $context)
    {
        // NOTE: Questa operazione deve essere splittata se le istruzioni sono più di 15k perchè altrimenti va in blocco
        if ($context->isGlobalOperation()) {
            $queries = Query::getGlobalProductPriceUpdateQuery($context->id_shop, $context->id_lang, $context->as_shop_id);
            $queries = explode(';', $queries);
            $query_size = count($queries);
            if ($query_size > 15000) {
                $start_cycle = 1;
                $end_cycle = ceil($query_size / 15000);
                for ($start = $start_cycle; $start <= $end_cycle; ++$start) {
                    $query = implode(';', array_slice($queries, 15000 * ($start - 1), 15000));
                    Queue::create($query, 0, $start, $end_cycle, $context->id_shop, $context->id_lang);
                }

                return $this;
            }
            $this->queries = implode(';', $queries);
            $update_row->unsetOperationIfExist('i');
            $update_row->unsetOperationIfExist('d');
            $update_row->unsetOperationIfExist('u');
        }

        if ($update_row->isInsertOperation()) {
            [
                'id_product' => $row_id_product,
                'id_product_attribute' => $row_id_product_attribute
            ] = $update_row->getRow()['i']['id_product']['raw'];

            $this->queries .= Query::getProductPriceUpdateQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang);
        }

        if ($update_row->isDeleteOperation()) {
            [
                'id_product' => $row_id_product,
                'id_product_attribute' => $row_id_product_attribute
            ] = $update_row->getRow()['d']['id_specific_price']['raw'];

            $this->queries .= Query::getProductPriceUpdateQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang);
        }

        if ($update_row->isUpdateOperation()) {
            [
                'id_product' => $row_id_product,
                'id_product_attribute' => $row_id_product_attribute
            ] = $update_row->getRow()['u']['id_product']['raw'];

            $this->queries .= Query::getProductPriceUpdateQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang);
        }

        return $this;
    }

    public function getQueries(): string
    {
        return $this->queries;
    }
}

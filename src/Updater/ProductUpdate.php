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

class ProductUpdate extends UpdateOperationAbstract implements OperationInterface
{
    private $queries = '';

    public function __construct()
    {
        $this->setName('product');
    }

    public function generateQueries(UpdateRow $update_row, UpdateContext $context)
    {
        $id_product = $context->id_product;

        if ($update_row->isDeleteOperation()) {
            $this->queries .= Query::getByName('remote_product_delete', [
                'product_external_id_str' => $context->buildExternalId([$id_product, 0]),
            ]);
        }

        if ($update_row->isInsertOperation()) {
            $update_row->unsetOperationIfExist('u');
            $update_row->removeFromStack('image');
            $update_row->removeFromStack('stock');
            $update_row->removeFromStack('price');
            $update_row->removeFromStack('category_association');
            $update_row->removeFromStack('attribute_image');
            $update_row->removeFromStack('variant');
            $this->queries .= Query::getProductCreationQuery($id_product, $context->id_shop, $context->id_lang, $context->as_shop_id, $context->as_shop_real_id);
        }

        if ($update_row->isUpdateOperation()) {
            foreach ($update_row->getRow()['u'] as $entity => $update) {
                $this->queries .= Query::getProductUpdateQueryByEntity($update['raw'], $context->id_shop, $context->id_lang);
            }
        }

        return $this;
    }

    public function getQueries(): string
    {
        return $this->queries;
    }
}

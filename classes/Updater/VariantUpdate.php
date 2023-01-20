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

use AccelaSearch\Query;

class VariantUpdate extends UpdateOperation implements Operation
{
    private $queries = '';

    public function __construct()
    {
        $this->setName('variant_update');
    }

    public function generateQueries(UpdateRow $update_row, UpdateContext $context)
    {
        $id_product = $context->id_product;
        $id_product_attribute = $context->id_product_attribute;

        if ($update_row->isInsertOperation()) {
            $this->queries .= Query::transformProductAndCreateVariant($id_product, $id_product_attribute, $context->id_shop, $context->id_lang, $context->as_shop_id);
        }

        if ($update_row->isDeleteOperation()) {
            $externalidstr = $context->buildExternalId([$id_product, $id_product_attribute]);
            $this->queries .= "UPDATE products SET deleted = 1 WHERE externalidstr = '$externalidstr';";
        }

        return $this;
    }

    public function getQueries(): string
    {
        return $this->queries;
    }
}

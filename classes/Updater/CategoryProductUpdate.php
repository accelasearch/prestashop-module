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

class CategoryProductUpdate extends UpdateOperation implements Operation
{
    private $queries = '';

    public function __construct()
    {
        $this->setName('category_product');
    }

    public function generateQueries(UpdateRow $update_row, UpdateContext $context)
    {
        $id_product = $context->id_product;
        $id_product_attribute = $context->id_product_attribute;

        if ($update_row->isDeleteOperation()) {
            foreach ($update_row->getRow()['d'] as $id_category_str => $cat_update) {
                [
                    'value' => $id_category,
                    'id_product' => $id_product
                ] = $cat_update['raw'];
                $lastupdate = date('Y-m-d H:i:s');
                $externalidstr = $context->buildExternalId([$id_category]);
                $ext_product_idstr = $context->buildExternalId([$id_product, 0]);
                $this->queries .= "UPDATE products_categories SET deleted = 1, lastupdate = '$lastupdate' WHERE productid = (SELECT id FROM products WHERE externalidstr = '$ext_product_idstr') AND categoryid = (SELECT id FROM categories WHERE externalidstr = '$externalidstr');";
            }
        }

        if ($update_row->isInsertOperation()) {
            foreach ($update_row->getRow()['i'] as $id_category_str => $cat_update) {
                [
                    'value' => $id_category,
                    'id_product' => $id_product
                ] = $cat_update['raw'];
                $lastupdate = date('Y-m-d H:i:s');
                $externalidstr = $context->buildExternalId([$id_category]);
                $ext_product_idstr = $context->buildExternalId([$id_product, 0]);
                $id_association = \AS_Collector::getInstance()->getValue("SELECT id FROM products_categories WHERE productid = (SELECT id FROM products WHERE externalidstr = '$ext_product_idstr') AND categoryid = (SELECT id FROM categories WHERE externalidstr = '$externalidstr')");
                if (!$id_association) {
                    $this->queries .= "INSERT INTO products_categories (categoryid, productid) VALUES ((SELECT id FROM categories WHERE externalidstr = '$externalidstr'),(SELECT id FROM products WHERE externalidstr = '$ext_product_idstr'));";
                } else {
                    $this->queries .= "UPDATE products_categories SET deleted = 0, lastupdate = '$lastupdate' WHERE id = $id_association;";
                }
            }
        }

        return $this;
    }

    public function getQueries(): string
    {
        return $this->queries;
    }
}

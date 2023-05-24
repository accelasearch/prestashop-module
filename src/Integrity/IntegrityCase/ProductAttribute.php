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

namespace AccelaSearch\Integrity\IntegrityCase;

use AccelaSearch;
use AccelaSearch\Collector;
use AccelaSearch\Integrity\Check;
use AccelaSearch\Integrity\Context;
use AccelaSearch\Query\Query;

class ProductAttribute extends IntegrityCaseAbstract implements Check
{
    const NAME = 'Product Attribute Check';

    public function check(): array
    {
        foreach ($this->context as $context) {
            $attributes = Collector::getInstance()->executeS("SELECT * FROM products_attr_label WHERE storeviewid = $context->as_shop_id");
            if (count($attributes) == 0) {
                $context->setResult(false);
            }
        }
        $this->check_results = $this->context;

        return $this->context;
    }

    public function fix(Context $context): bool
    {
        $queries = '';
        $queries .= Query::getByName('shopInitializationsAttributes_query', [
            'storeview_id' => $context->as_shop_id,
            'externalidstr_warehouse' => $context->id_shop . '_' . $context->id_lang,
        ]);
        $queries .= AccelaSearch::generateVariantsQuery($context->as_shop_id, $context->id_lang);
        $queries .= AccelaSearch::generateFeaturesQuery($context->as_shop_id, $context->id_lang);
        try {
            Collector::getInstance()->beginTransaction();
            Collector::getInstance()->exec($queries);
            Collector::getInstance()->commit();
        } catch (\Throwable $th) {
            Collector::getInstance()->rollback();
            \Db::getInstance()->insert('log', [
                'severity' => 1,
                'error_code' => 0,
                'message' => pSQL($th->getMessage()),
            ]);

            return false;
        }

        return true;
    }
}

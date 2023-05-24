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
include_once dirname(__FILE__) . '/../../config/config.inc.php';

$token = Tools::getValue('token', null);
$wait = Tools::getValue('wait', true);
$origin = Tools::getValue('origin', 'cronjob');
if ($token === Configuration::get('ACCELASEARCH_CRON_TOKEN')) {
    $accelasearch = Module::getInstanceByName('accelasearch');
    if ($accelasearch->active) {
        if ($origin !== 'pageview') {
            Configuration::updateGlobalValue('ACCELASEARCH_LAST_CRONJOB_EXECUTION', time());
        }
        $last_hourly_check = (int) Configuration::get('ACCELASEARCH_LAST_HOURLY_CHECK');
        // if initial sync was completed and has passed more than 1 hour since last hourly check
        if ((!$last_hourly_check || $last_hourly_check < time() - 3600) && (int) Configuration::get('ACCELASEARCH_FULLSYNC_CREATION_PROGRESS') === 2) {
            Configuration::updateGlobalValue('ACCELASEARCH_LAST_HOURLY_CHECK', time());
            $as_shops = AccelaSearch::getAsShops();
            $processed = [];
            $queries = [];
            foreach ($as_shops as $as_shop) {
                [
                    'id_shop' => $id_shop,
                    'id_lang' => $id_lang,
                    'as_shop_id' => $as_shop_id,
                    'as_shop_real_id' => $as_shop_real_id
                ] = $as_shop;
                AccelaSearch::createQueryDataInstanceByIdShopAndLang($id_shop, $id_lang, $as_shop_id, $as_shop_real_id);
                $missings = AccelaSearch::getMissingProductsOnAs($id_shop, $id_lang);
                foreach ($missings as $missing) {
                    [$id_shop, $id_lang, $id_product, $id_product_attribute] = explode('_', $missing);
                    if (in_array($id_product, $processed)) {
                        continue;
                    }
                    $queries[$id_shop . '_' . $id_lang][] = AccelaSearch\Query\Query::getProductCreationQuery($id_product, $id_shop, $id_lang, $as_shop_id, $as_shop_real_id, AccelaSearch::WITHOUT_IGNORE);
                    if (!(bool) $id_product_attribute) {
                        $processed[] = $id_product;
                    }
                }
            }

            foreach ($queries as $id_shop_and_lang => $query_set) {
                [$id_shop, $id_lang] = explode('_', $id_shop_and_lang);
                $divider = AccelaSearch\Queue::getOffsetDividerByType('PRODUCT', count($query_set));
                $queries_set = array_chunk($query_set, $divider);
                $i = 1;
                $end_cycle = count($queries_set);
                foreach ($queries_set as $query) {
                    $limit = $divider * ($i - 1) . ',' . $divider;
                    AccelaSearch\Queue::create(implode('', $query), $limit, $i, $end_cycle, $id_shop, $id_lang);
                    ++$i;
                }
            }
            exit('Hourly check executed');
        }
        $cron = AccelaSearch::hookActionCronjobStatic($wait);
        var_dump($cron);
    }
}

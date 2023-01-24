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
$token = Tools::getValue("token", null);
$wait = Tools::getValue("wait", true);
$origin = Tools::getValue("origin", "cronjob");
if ($token === Configuration::get('ACCELASEARCH_CRON_TOKEN')) {
    $accelasearch = Module::getInstanceByName('accelasearch');
    if ($accelasearch->active) {
        if ($origin !== 'pageview') {
            Configuration::updateGlobalValue('ACCELASEARCH_LAST_CRONJOB_EXECUTION', time());
        }
        $accelasearch->hookActionCronJob($wait);
    }
}

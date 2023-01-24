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
require_once __DIR__ . '/classes/AS_Collector.php';
require_once __DIR__ . '/classes/Query.php';
require_once __DIR__ . '/classes/QueryData.php';
require_once __DIR__ . '/classes/Queue.php';
require_once __DIR__ . '/classes/Sync.php';
require_once __DIR__ . '/classes/Translator.php';
require_once __DIR__ . '/classes/Trigger.php';
require_once __DIR__ . '/classes/TriggerData.php';
require_once __DIR__ . '/classes/TriggerDataElements.php';

require_once __DIR__ . '/classes/Updater/UpdateOperationAbstract.php';
require_once __DIR__ . '/classes/Updater/OperationInterface.php';
require_once __DIR__ . '/classes/Updater/RowOperationsInterface.php';
require_once __DIR__ . '/classes/Updater/AttributeImageUpdate.php';
require_once __DIR__ . '/classes/Updater/CategoryProductUpdate.php';
require_once __DIR__ . '/classes/Updater/CategoryUpdate.php';
require_once __DIR__ . '/classes/Updater/ImageUpdate.php';
require_once __DIR__ . '/classes/Updater/PriceUpdate.php';
require_once __DIR__ . '/classes/Updater/ProductUpdate.php';
require_once __DIR__ . '/classes/Updater/StockUpdate.php';
require_once __DIR__ . '/classes/Updater/UpdateContext.php';
require_once __DIR__ . '/classes/Updater/Updater.php';
require_once __DIR__ . '/classes/Updater/UpdateRow.php';
require_once __DIR__ . '/classes/Updater/VariantUpdate.php';
require_once __DIR__ . '/classes/Updater/FeatureUpdate.php';

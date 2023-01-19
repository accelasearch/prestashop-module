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


namespace AccelaSearch;

class Translator extends \Module
{
    private static $instance = null;
    public $translation_array;

    public function __construct()
    {
        $this->translation_array = [
          'cron_setup_title' => $this->l('Cronjob Setup'),
          'cron_setup_description' => $this->l("We reccomend you to setup a cronjob to execute every 1 minute (we don't take any action if not necessary), paste the script below in your cronjob manager"),
          'cron_setup_notice' => $this->l('Note if you reinstall the module, cronjob token will change'),
          'got_it' => $this->l('Got it!'),
          'good_job' => $this->l('Good job!'),
          'oops' => $this->l('Oops.. An error occurred'),
          'select_at_least_1_shop' => $this->l('Select at least 1 shop and language'),
          'shop_sync_failed' => $this->l('Failed to sync shops'),
          'shop_sync_success' => $this->l('Shop inserted successfully! Wait a second...'),
          'api_key_error' => $this->l('Api Key check failed, try again or contact support'),
          'start_remote_checker_success' => $this->l('All products are present and already updated on AccelaSearch'),
          'resync_users_groups_success' => $this->l('Users Groups resynced successfully, refresh the page to see changes'),
          'resync_all_success' => $this->l('Resync has started successfully! You will search your products in a few minutes'),
          'resync_all_fail' => $this->l('An error occurred with resync initialization, try again'),
          'close' => $this->l('Close'),
          'no_logs' => $this->l('No logs to show'),
          'see_log' => $this->l('Show log'),
          'resync_all' => $this->l('Resync all Products'),
          'resync_areusure' => $this->l('Are you sure you want to resync all products? Type'),
          'resync_inthefield' => $this->l('in the field below to confirm'),
          'resync_price_success' => $this->l('Price resync started, wait some minutes...'),
          'resync_price_fail' => $this->l('An error occurred with a Price Resync initializations, try again'),
          'cancel' => $this->l('Cancel'),
          'disconnect_apikey_title' => $this->l('Disconnect ApiKey'),
          'disconnect_success' => $this->l('Your disconnect process is started, now you can put a new ApiKey to this instance, wait a seconds...'),
          'apikey_connection_success' => $this->l('ApiKey connected successfully! Wait a seconds...'),
          'disconnect_areusure' => $this->l('Are you sure you want to disconnect ApiKey? This process remove all products from AccelaSearch and all of your configuration will be reset. Type'),
        ];
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Translator();
        }

        return self::$instance;
    }
}

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

namespace AccelaSearch\Integrity;

/**
 * Class to manage integrity checks
 */
class Integrity
{
    private $checks = [];
    private $results = [];

    /**
     * Register a new check case
     */
    public function register(Check $check)
    {
        $this->checks[] = $check;

        return $this;
    }

    /**
     * Iterate over all registered checks and execute them
     */
    public function performs()
    {
        foreach ($this->checks as $check) {
            $this->results[get_class($check)] = $check->check();
        }

        return $this->results;
    }

    /**
     * Iterate over all registered checks and execute the fix method for each context that has failed
     */
    public function fixes()
    {
        foreach ($this->results as $class => $contexts) {
            foreach ($contexts as $context) {
                if (!$context->getResult()) {
                    $check = new $class();
                    $check->fix($context);
                }
            }
        }
    }

    /**
     * Return the results of the last performed checks
     */
    public function logs()
    {
        return $this->results;
    }
}

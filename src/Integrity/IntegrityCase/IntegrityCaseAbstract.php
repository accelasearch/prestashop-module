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

use AccelaSearch\Integrity\Check;
use AccelaSearch\Integrity\Context;

/**
 * Abstract class for integrity cases
 *
 * When instantiating a new integrity case, the constructor will automatically create a context for each shop and language
 */
abstract class IntegrityCaseAbstract implements Check
{
    public $check_results = [];
    public $fix_results = [];
    public $context = [];
    const FIX_ALLOWED = true;
    const NAME = 'Default integrity case name';

    public function __construct()
    {
        $shops = \AccelaSearch::getAsShops();
        foreach ($shops as $shop) {
            [
                'id_shop' => $id_shop,
                'id_lang' => $id_lang,
                'as_shop_id' => $as_shop_id,
                'as_shop_real_id' => $as_shop_real_id,
            ] = $shop;
            $context = new Context($id_shop, $id_lang, $as_shop_id, $as_shop_real_id);
            $this->context[] = $context;
        }
    }

    /**
     * Check the integrity of the case, the check should be executed for each shop and language
     *
     * @return array
     */
    abstract public function check(): array;

    /**
     * Determine the action to be taken to fix the integrity issue
     *
     * @param Context $context
     *
     * @return bool
     */
    abstract public function fix(Context $context): bool;
}

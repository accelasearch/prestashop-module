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

class Context
{
    public $id_shop;
    public $id_lang;
    public $as_shop_id;
    public $as_shop_real_id;
    public $result = true;

    public function __construct($id_shop, $id_lang, $as_shop_id, $as_shop_real_id)
    {
        $this->id_shop = $id_shop;
        $this->id_lang = $id_lang;
        $this->as_shop_id = $as_shop_id;
        $this->as_shop_real_id = $as_shop_real_id;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }
}

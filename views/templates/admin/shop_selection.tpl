{**
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
 *}
{extends file="./main.tpl"}
{block name=as_content}
  <div class="flex min-h-[50vh] mb-5">
    <div class="m-auto">
      <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 rounded bg-white">
        <div class="max-w-md w-full space-y-8 bg">
          <div>
            <img class="mx-auto h-12 w-auto" src="{$module_url}/views/img/as-logo.svg" alt="AccelaSearch Logo">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
              {l s='Select shops and languages' mod='accelasearch'}</h2>
            <p class="mt-3 text-center text-sm text-gray-600">
              {l s='What shops and languages you want to use on AccelaSearch?' mod='accelasearch'}
            </p>
          </div>
          <form class="mt-6 space-y-6" action="#" method="POST" id="as_shop_selection_form">
            <div>
              {foreach from=$as_shops item=cur_shop}
                {foreach from=$cur_shop.languages item=lang}
                  <div class="flex items-center">
                    <input id="as_shop_to_sync_{$cur_shop.id_shop}_{$lang.id_lang}"
                      name="as_shop_to_sync_{$cur_shop.id_shop}_{$lang.id_lang}" type="checkbox"
                      class="h-4 w-4 text-as-primary-400 focus:ring-as-primary-400 border-gray-300 rounded as_shop_to_sync"
                      data-id-shop="{$cur_shop.id_shop}" data-id-lang="{$lang.id_lang}">
                    <label for="as_shop_to_sync_{$cur_shop.id_shop}_{$lang.id_lang}" class="ml-3 block mt-2 text-gray-600">
                      <span class="text-gray-500 font-thin">{$lang.name}</span> {$cur_shop.name}
                    </label>
                  </div>
                {/foreach}
              {/foreach}
            </div>
            <div>
              <button type="submit" class="as-btn-primary" id="as_shop_selection_cta">
                <span class="text-[14px]">{l s='Start products sync' mod='accelasearch'}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
{/block}
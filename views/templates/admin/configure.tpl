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
<div id="mainstage">
  <div class="flex min-h-[50vh]">
    <div class="m-auto w-full">
      <div class="intro bg-white p-8 relative overflow-hidden">
        <div class="absolute top-[-1123px] right-[-10px] w-[780px] rotate-[-30deg] opacity-0 sm:opacity-100 invisible sm:visible">
          <img src="{$module_url}/views/img/as_sfondo.svg" alt="AccelaSearch">
        </div>
        <div>
          <img src="{$module_url}/views/img/as-logo.svg" width="224" height="61" alt="AccelaSearch">
        </div>
        <div class="mt-3">
          <p class="text-xl text-gray-800 font-bold">
            {l s='Boost your search engine without knowing one line code!' mod='accelasearch'}
          </p>
        </div>
        <div class="mt-6">
          <button type="button" class="as-btn-primary start_now w-auto">
            <span class="text-[14px]">{l s='Start now!' mod='accelasearch'}</span>
          </button>
        </div>
        <div class="mt-4">
          <div class="text-xs text-gray-600">
            <ul class="mb-4">
              <li class="inline pl-3 pr-4 assure_checkbox">
                {l s='No credit card required' mod='accelasearch'}
              </li>
              <li class="inline pl-3 pr-4 assure_checkbox">
                {l s='No coding skill required' mod='accelasearch'}
              </li>
              <li class="inline pl-3 pr-4 assure_checkbox">
                {l s='Easy to configure' mod='accelasearch'}
              </li>
            </ul>
          </div>
        </div>
        <div class="relative my-4 min-h-[30px]">
          <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-gray-300"></div>
          </div>
        </div>
        <div>
          <div class="sm:flex sm:flex-wrap px-12 my-12">
            <div class="sm:w-[25%]">
              <img src="{$module_url}/views/img/relevant-search.svg" />
            </div>
            <div class="pl-12 sm:w-[75%] flex">
              <div class="m-auto justify-center items-center">
                <p class="text-xl text-gray-700 font-bold">
                  {l s='AI Search Engine to show search results never seen before' mod='accelasearch'}
                </p>
                <p class="text-lg text-gray-600">
                  {l s='Giving your users the ability to find what they are looking for in a much simpler and AI-powered way means increasing the value of your products through faster and more relevant searches and results.' mod='accelasearch'}
                </p>
              </div>
            </div>
          </div>
          <div class="sm:flex sm:flex-wrap px-12 my-12">
            <div class="sm:w-[25%]">
              <img src="{$module_url}/views/img/insights.svg" />
            </div>
            <div class="pl-12 sm:w-[75%] flex">
              <div class="m-auto justify-center items-center">
                <p class="text-xl text-gray-700 font-bold">
                  {l s='Collect valuable information from your users every day' mod='accelasearch'}
                </p>
                <p class="text-lg text-gray-600">
                  {l s='Learn from your users\' behavior. Get to know their most searched and clicked products in a chosen time period. Learn more about your products and get all the information you were missing.' mod='accelasearch'}
                </p>
              </div>
            </div>
          </div>
          <div class="sm:flex sm:flex-wrap px-12 my-12">
            <div class="sm:w-[25%]">
              <img src="{$module_url}/views/img/visual-ui.png" />
            </div>
            <div class="pl-12 sm:w-[75%] flex">
              <div class="m-auto justify-center items-center">
                <p class="text-xl text-gray-700 font-bold">
                  {l s='Create visual experiences without the use of code' mod='accelasearch'}
                </p>
                <p class="text-lg text-gray-600">
                  {l s='The No-code revolution has taken over and customizing your tools is more important than ever. AccelaSearch allows you to customize your search engine as you wish without relying on developers.' mod='accelasearch'}
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="relative my-4 min-h-[30px]">
          <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t border-gray-300"></div>
          </div>
        </div>
        <div class="mt-4 text-center">
          <p class="text-sm text-gray-600">
            {l s='Start using AccelaSearch now!' mod='accelasearch'}
          </p>
          <div class="mt-4">
            <button type="button" class="as-btn-primary start_now w-auto">
              <span class="text-[14px]">{l s='Start now!' mod='accelasearch'}</span>
            </button>
          </div>
        </div>
      </div>
      <div class="apikey-insert" style="display:none">
        <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 rounded bg-white">
          <div class="max-w-md w-full space-y-8 bg">
            <div>
              <img class="mx-auto h-12 w-auto" src="{$module_url}/views/img/as-logo.svg" alt="AccelaSearch Logo">
              <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">{l s='Link your account' mod='accelasearch'}</h2>
              <p class="mt-2 text-center text-sm text-gray-600">
                {l s='Copy your Api Key from AccelaSearch console and paste it below' mod='accelasearch'}
              </p>
            </div>
            <form class="mt-8 space-y-6" action="#" method="POST" id="as_apikey_form">
              <div class="rounded-md shadow-sm -space-y-px">
                <div>
                  <label for="email-address" class="sr-only">{l s='Api Key' mod='accelasearch'}</label>
                  <input id="apikey" name="apikey" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Api Key">
                </div>
              </div>
              <div>
                <button type="submit" class="as-btn-primary" id="as_apikey_cta">
                  <span class="text-[14px]">{l s='Link to AccelaSearch' mod='accelasearch'}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
{/block}

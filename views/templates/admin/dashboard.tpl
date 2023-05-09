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
  <div class="min-h-full">
    <nav class="bg-as-primary-400">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center w-full">
            <div class="flex-shrink-0">
              <img class="h-8 w-8" src="{$module_url}/views/img/favicon.png" alt="AccelaSearch">
            </div>
            <div class="as-hidden md:block">
              <div class="ml-10 flex items-baseline space-x-4" id="as-menu-nav">
                <a href="javascript:void(0)"
                  class="as-current-page no-underline bg-as-primary-700 text-white px-3 py-2 rounded-md text-sm font-medium"
                  page="dashboard">Dashboard</a>
                <a href="javascript:void(0)"
                  class="no-underline text-gray-100 hover:bg-as-primary-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
                  page="product_sync">{l s='Product Synchronization' mod='accelasearch'}</a>
                <a href="javascript:void(0)"
                  class="no-underline text-gray-100 hover:bg-as-primary-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
                  page="advanced">{l s='Advanced' mod='accelasearch'}</a>
                <a href="javascript:void(0)"
                  class="no-underline text-gray-100 hover:bg-as-primary-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
                  page="support">{l s='Support' mod='accelasearch'}</a>
              </div>
            </div>
            <div class="ml-auto">
              <div class="relative inline-block text-left">
                <div class="dropdown-container">
                  <button type="button" class="as-dropdown-style" aria-expanded="true" aria-haspopup="true">
                    {l s='Account' mod='accelasearch'}
                    <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                      fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
                <div
                  class="as-dropdown origin-top-right absolute right-0 mt-2 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none"
                  role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1"
                  style="display:none">
                  <div class="px-4 py-3" role="none">
                    <p class="text-sm" role="none">
                    <div class="flex">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                          d="M18 8a6 6 0 01-7.743 5.743L10 14l-1 1-1 1H6v2H2v-4l4.257-4.257A6 6 0 1118 8zm-6-4a1 1 0 100 2 2 2 0 012 2 1 1 0 102 0 4 4 0 00-4-4z"
                          clip-rule="evenodd" />
                      </svg>
                      <span class="ml-2">{l s='Your Api Key' mod='accelasearch'}</span>
                    </div>
                    </p>
                    <p class="text-sm font-medium text-gray-900" role="none">{$AS_apikey}</p>
                  </div>
                  <div class="py-1" role="none">
                    <button id="disconnect_apikey" type="submit"
                      class="text-red-600 block w-full text-left px-4 py-2 text-sm" role="menuitem"
                      tabindex="-1">{l s='Disconnect Api Key' mod='accelasearch'}</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>
    <main class="bg-white" id="as-pages">
      <div class="as-page" page="dashboard">
        <header>
          <div class="max-w-7xl mx-auto pt-6 pb-3 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
          </div>
        </header>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
          <div class="mt-2 grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-xl font-bold text-gray-700 truncate mb-2">{l s='Search Layer' mod='accelasearch'}
                      </dt>
                      <dd>
                        <div class="text-sm text-gray-500">
                          {l s='Configure where the search Layer appear in your website' mod='accelasearch'}
                        </div>
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
              <div class="px-5 py-3">
                <div class="text-sm">
                  <a href="https://console.accelasearch.io/setup"
                    class="no-underline font-medium text-as-primary-400 hover:text-as-primary-700 flex items-center justify-end"
                    target="_blank">
                    <span>Open configuration</span>
                    <span class="ml-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                          d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                          clip-rule="evenodd" />
                      </svg>
                    </span>
                  </a>
                </div>
              </div>
            </div>
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-xl font-bold text-gray-700 truncate mb-2">{l s='Cronjob' mod='accelasearch'}</dt>
                      <dd>
                        <div class="text-sm text-gray-500">
                          {l s='Cronjob is a scheduled operation.' mod='accelasearch'}
                          <br>
                          {l s='We recommend to use it to assure your data informations are updated frequently' mod='accelasearch'}
                        </div>
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
              <div class="px-5 py-3">
                <div class="text-sm">
                  <a href="javascript:void(0)"
                    class="no-underline font-medium text-as-primary-400 hover:text-as-primary-700 flex items-center justify-end showCronModal">
                    <span>How to setup cronjob</span>
                    <span class="ml-2">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                          d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                          clip-rule="evenodd" />
                      </svg>
                    </span>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="as-page as-hidden" page="product_sync">
        <header>
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900">Product sync</h1>
          </div>
        </header>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
          <div class="px-4 pb-6 sm:px-0">
            {if $PRODUCTS_SYNC_NEVER_STARTED}
              <div id="state-never-started">
                <div class="notification my-4">
                  <div class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                          fill="currentColor" aria-hidden="true">
                          <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                        </svg>
                      </div>
                      <div class="ml-3 flex-1 md:flex md:justify-between">
                        <p class="text-sm text-red-700">
                          {l s='Your products sync never started, setup the cronjob to start products syncing. While you setup the cronjob we will trigger it by pageview but this is not a recommended  and accurately way, setup the cronjob as soon as possible.' mod='accelasearch'}
                        </p>
                        <p class="mt-3 text-sm md:mt-0 md:ml-6">
                          <a href="javascript:void(0)"
                            class="whitespace-nowrap font-medium text-red-700 hover:text-red-600 showCronModal">{l s='How to setup' mod='accelasearch'}
                            <span aria-hidden="true">&rarr;</span></a>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            {/if}
            {if $PRODUCTS_SYNC_PROGRESS}
              <div id="state-processing">
                <div class="flex items-center mb-8">
                  <div>
                    <div class="lds-ring branded">
                      <div></div>
                      <div></div>
                      <div></div>
                      <div></div>
                    </div>
                  </div>
                  <div class="ml-4">
                    <span class="text-gray-500">
                      {l s='Products Syncing on AccelaSearch, come back after the sync process is completed' mod='accelasearch'}
                    </span>
                  </div>
                </div>
              </div>
            {/if}
            {if $PRODUCTS_SYNC_COMPLETED}
              <div id="state-completed">
                <div class="notification my-4">
                  <div class="rounded-md bg-green-50 p-4">
                    <div class="flex">
                      <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                          fill="currentColor" aria-hidden="true">
                          <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                        </svg>
                      </div>
                      <div class="ml-3 flex-1 md:flex md:justify-between">
                        <p class="text-sm text-green-700">
                          {l s='Good job! Your products are synced to AccelaSearch and updated' mod='accelasearch'}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="p-6">
                <div class="text-center">
                  <div id="no-issues">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 modal-title">
                      {l s='No products issues found' mod='accelasearch'}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                      {l s='Launch a manual check if you got a products informations issue' mod='accelasearch'}.<br>
                      {l s='This may happens if you temporary disconnect or deactivate AccelaSearch module and reactivate/reinstall it' mod='accelasearch'}.
                    </p>
                  </div>
                  <div id="with-issues" style="display:none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-red-600" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 modal-title">
                      {l s='Some product informations are missing' mod='accelasearch'}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                      {l s='There are some conflicts with your products data and AccelaSearch data' mod='accelasearch'}.<br>
                      {l s='This may happens if you have same products reference or corrupted data in your store, please check the error log' mod='accelasearch'}.
                    </p>
                  </div>
                  <div class="mt-6">
                    <button id="start_remote_checker" type="button"
                      class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-as-primary-400 hover:bg-as-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-as-primary-400">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path
                          d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2h-1.528A6 6 0 004 9.528V4z" />
                        <path fill-rule="evenodd"
                          d="M8 10a4 4 0 00-3.446 6.032l-1.261 1.26a1 1 0 101.414 1.415l1.261-1.261A4 4 0 108 10zm-2 4a2 2 0 114 0 2 2 0 01-4 0z"
                          clip-rule="evenodd" />
                      </svg>
                      {l s='Start Check' mod='accelasearch'}
                    </button>
                  </div>
                </div>
                <div class="missing-response"></div>
              </div>
            {/if}
          </div>
        </div>
      </div>
      <div class="as-page as-hidden" page="advanced">
        <header>
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900">{l s='Advanced' mod='accelasearch'}</h1>
          </div>
        </header>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
          <div class="mt-2 grid grid-cols-1 gap-5 lg:grid-cols-2">
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-xl font-bold text-gray-700 truncate mb-2">{l s='Users groups' mod='accelasearch'}
                      </dt>
                      <dd>
                        {if count($MISSING_USERS_GROUPS) > 0}
                          <div class="md:flex md:items-center md:justify-between">
                            <div class="flex-1 min-w-0">
                              <p class="text-sm text-gray-500">
                                {l s='Some users groups are missing in AccelaSearch' mod='accelasearch'}
                              </p>
                            </div>
                            <div class="mt-4 flex md:mt-0 md:ml-4">
                              <button id="resync_users_groups" type="button"
                                class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-as-primary-400 hover:bg-as-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-as-primary-400">{l s='Re-sync all groups' mod='accelasearch'}</button>
                            </div>
                          </div>
                        {else}
                          <div class="md:flex md:items-center md:justify-between">
                            <div class="flex-1 min-w-0">
                              <p class="text-sm text-gray-500">
                                {l s='All users groups are synced successfully' mod='accelasearch'}
                              </p>
                            </div>
                          </div>
                        {/if}
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-xl font-bold text-gray-700 truncate mb-2">
                        {l s='Resync all products' mod='accelasearch'}</dt>
                      <dd>
                        <div class="text-sm text-gray-500">
                          {l s='In case you have uninstalled and reinstalled the module or got products informations broken you can resync all products informations.' mod='accelasearch'}<br><br>
                          {l s='Before doing this, check your cronjobs are working properly.' mod='accelasearch'}<br>
                          {l s='This procedure will take some minutes depends on your store catalog.' mod='accelasearch'}
                        </div>
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
              <div class="px-5 py-3">
                <div class="text-sm text-right">
                  <button type="button"
                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-600 mr-2"
                    id="resync_all_products">{l s='DELETE AND FULLSYNC CATALOG' mod='accelasearch'}</button>
                </div>
              </div>
            </div>
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                  </div>
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-xl font-bold text-gray-700 truncate mb-2">
                        {l s='Resync all prices' mod='accelasearch'}</dt>
                      <dd>
                        <div class="text-sm text-gray-500">
                          {l s='Trigger a full price resync. Update all prices with a last product original price and special price on AccelaSearch.' mod='accelasearch'}
                        </div>
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
              <div class="px-5 py-3">
                <div class="text-sm text-right">
                  <button type="button"
                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-600 mr-2"
                    id="resync_all_prices">{l s='RESYNC ALL PRICES' mod='accelasearch'}</button>
                </div>
              </div>
            </div>
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
              <div class="p-5">
                <div class="flex items-center">
                  <div class="ml-5 w-0 flex-1">
                    <dl>
                      <dt class="text-xl font-bold text-gray-700 truncate mb-2">{l s='Shops Synced' mod='accelasearch'}
                      </dt>
                      <dd>
                        {if count($MISSING_SHOPS) > 0}
                          <div class="md:flex md:items-center md:justify-between">
                            <div class="flex-1 min-w-0">
                              <p class="text-sm text-gray-500">
                                {l s='There are some shops/languages that are not configured on AccelaSearch, if this was your choice don\'t take any action.' mod='accelasearch'}
                              </p>
                            </div>
                            <div class="mt-4 flex md:mt-0 md:ml-4">
                              <button id="trigger_shop_selections" type="button"
                                class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-as-primary-400 hover:bg-as-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-as-primary-400">{l s='Select newests' mod='accelasearch'}</button>
                            </div>
                          </div>
                        {else}
                          <div class="md:flex md:items-center md:justify-between">
                            <div class="flex-1 min-w-0">
                              <p class="text-sm text-gray-500">
                                {l s='You have synced all shops/languages available on your e-commerce, good job!' mod='accelasearch'}
                              </p>
                            </div>
                          </div>
                        {/if}
                      </dd>
                    </dl>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="px-4 py-6 sm:px-0">
            {if $DEBUG_MODE}
              <div class="flex my-2">
                <button type="button"
                  class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 mr-2"
                  id="shop_initializations">Shop initializations</button>
                <button type="button"
                  class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 mr-2"
                  id="generate_products_query">Generate products query</button>
                <button type="button"
                  class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 mr-2"
                  id="generate_products_queue_query">Generate products queue query</button>
                <button type="button"
                  class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 mr-2"
                  id="cleanup_products">Clean up products</button>
                <button type="button"
                  class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-orange-800 hover:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-800 mr-2"
                  id="delete_queue">Delete queue</button>
                <button type="button"
                  class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 mr-2"
                  id="resync_all_prices">Resync all prices</button>
              </div>
            {/if}
          </div>
          {if $DEBUG_MODE}
            <div>
              <div class="mt-1 flex rounded-md shadow-sm">
                <div class="relative flex items-stretch flex-grow focus-within:z-10">
                  <input type="text" name="as_pid" id="as_pid"
                    class="focus:ring-indigo-500 focus:border-indigo-500 block w-full rounded-none rounded-l-md pl-10 sm:text-sm border-gray-300"
                    placeholder="Get AccelaSearch updated informations by PrestaShop product ID">
                </div>
                <button id="get_as_informations" type="button"
                  class="-ml-px relative inline-flex items-center space-x-2 px-4 py-2 border border-gray-300 text-sm font-medium rounded-r-md text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                  <span>Get Informations</span>
                </button>
              </div>
            </div>
          {/if}
          <div class="bg-white" id="as-product-container">

          </div>
        </div>
      </div>
      <div class="as-page as-hidden" page="support">
        <header>
          <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900">{l s='Support' mod='accelasearch'}</h1>
          </div>
        </header>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
          <div class="px-4 py-6 sm:px-0">
            <div class="p-3">
              <div class="flex flex-wrap">
                <div class="w-full sm:w-1/2 flex">
                  <div style="max-width: 96px;">
                    <img src="{$module_url}logo.png" />
                  </div>
                  <div>
                    <p class="">
                      <span
                        class="text-xl font-extrabold text-gray-900 sm:text-2xl">{l s='What can you do with this module' mod='accelasearch'}</span>
                    <ul class="pl-4 pt-4">
                      <li class="pb-1">{l s='Boost your search engine' mod='accelasearch'}</li>
                      <li class="pb-1">{l s='AccelaSearch increase your conversions' mod='accelasearch'}</li>
                      <li class="pb-1">{l s='AccelaSearch increase your conversions' mod='accelasearch'}</li>
                      <li class="pb-1">{l s='AccelaSearch increase your conversions' mod='accelasearch'}</li>
                    </ul>
                    </p>
                  </div>
                </div>
                <div class="w-full sm:w-1/2 px-4">
                  <div class="bg-gray-100 p-8 text-center">
                    <p class="text-center text-gray-400 pb-4 font-bold text-l">
                      {l s='Need help? Read a module documentation' mod='accelasearch'}
                    </p>
                    <a href="{$module_url}docs.pdf"
                      class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-as-primary-400 hover:bg-as-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-as-primary-400">Open
                      PDF</a>
                  </div>
                  <div class="text-center mt-6">
                    <div>{l s='Can\'t find a solution to your problem?' mod='accelasearch'}</div>
                    <div class="">
                      <a href="https://dgcal.atlassian.net/servicedesk/customer/portal/6" target="_blank"
                        class="btn btn-link flex justify-center items-center text-as-primary-400">
                        Open a support Ticket
                        <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-6 w-6" fill="none" viewBox="0 0 24 24"
                          stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="">
                <div id="faqs-wrapper">
                  <div class="my-20 text-center">
                    <div class="lds-ring branded">
                      <div></div>
                      <div></div>
                      <div></div>
                      <div></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
{/block}
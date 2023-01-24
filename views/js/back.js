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
const AS = {
  endpoint: "https://svc11.accelasearch.net/API/",
  controller: async (action = "", method = "GET", data) => {
    const _data = {
      ajax: 1,
      action: action,
      ...data,
    };

    return await $.ajax({
      type: method,
      cache: false,
      dataType: "json",
      url: as_admin_controller,
      data: _data,
    });
  },
  api: async (action = "", method = "GET", is_auth = false) => {
    const headers = {};
    if (is_auth) {
      headers["X-Accelasearch-Apikey"] = _AS.apikey;
    }
    const fetchObject = {};
    fetchObject["method"] = method;
    fetchObject["headers"] = new Headers(headers);
    return await fetch(AS.endpoint + controller, fetchObject);
  },
  helpers: {
    seeLog: (k) => {
      const modal_content = `
      <div class="mt-5 sm:mt-6">
        <button type="button" class="dismiss-modal inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-as-primary-400 text-base font-medium text-white hover:bg-as-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-as-primary-400 sm:text-sm">${_AS.translations.close}</button>
      </div>
      `;
      if (!(k in AS.last_errors)) {
        AS.helpers.modal(
          "<div>" + _AS.translations.no_logs + "</div>" + modal_content
        );
        return;
      }
      AS.helpers.modal("<div>" + AS.last_errors[k] + "</div>" + modal_content);
    },
    toast_timeout: "",
    modal: (content) => {
      const modal_content = `
      <div class="modal-container relative z-[501]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed z-[501] inset-0 overflow-y-auto">
          <div class="flex items-end sm:items-center justify-center min-h-full p-4 text-center sm:p-0">
            <div class="modal-content relative bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-sm sm:w-full sm:p-6">
              ${content}
            </div>
          </div>
        </div>
      </div>
      `;
      $("#as-backdrop").show(0);
      $("#as-modals").html(modal_content);
      $(".dismiss-modal").on("click", function () {
        $(this).closest(".modal-container").remove();
        $("#as-backdrop").hide(0);
      });
    },
    toast: (msg, type = "SUCCESS", duration = 7000) => {
      const title =
        type == "SUCCESS" ? _AS.translations.good_job : _AS.translations.oops;
      const icon =
        type == "SUCCESS"
          ? `<svg class="h-6 w-6 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>`
          : `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
      </svg>`;
      const toast_html = `
        <div class="toast_size right-[10px] fixed z-20 bottom-[20px] lg:top-[140px] lg:bottom-[unset] lg:right-[20px] toast translate-y-2 sm:translate-y-0 sm:translate-x-2 transform ease-out duration-300 transition max-w-sm bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
          <div class="p-4">
            <div class="flex items-start">
              <div class="flex-shrink-0">
              ${icon}
              </div>
              <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-semibold text-gray-900">${title}</p>
                <p class="mt-1 text-sm text-gray-500">${msg}</p>
              </div>
              <div class="ml-4 flex-shrink-0 flex">
                <button type="button" class="dismiss-toast bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-as-primary-400">
                  <span class="sr-only">Close</span>
                  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      `;
      clearTimeout(AS.helpers.toast_timeout);
      $("#as-toaster").html(toast_html);
      $("#as-toaster .toast").addClass("as-toast-show");
      AS.helpers.toast_timeout = setTimeout(() => {
        $("#as-toaster").html("");
      }, duration);
      $(".dismiss-toast").on("click", function () {
        $("#as-toaster").html("");
        clearTimeout(AS.helpers.toast_timeout);
      });
    },
    buttonContent: {},
    load: (el) => {
      const originalButtonContent = $(el).html();
      AS.helpers.buttonContent[el] = originalButtonContent;
      $(el).addClass("cursor-not-allowed");
      $(el).attr("disabled", "disabled");
      const loaderHtmlContent = `
        <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
      `;
      $(el).html(loaderHtmlContent);
    },
    unload: (el) => {
      const originalButtonContent = AS.helpers.buttonContent[el];
      $(el).removeClass("cursor-not-allowed");
      $(el).removeAttr("disabled");
      $(el).html(originalButtonContent);
    },
  },
};

(function () {
  "use strict";
  console.trace = null;

  const menu_classes = {
    default:
      "no-underline text-gray-100 hover:bg-as-primary-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium",
    selected:
      "as-current-page no-underline bg-as-primary-700 text-white px-3 py-2 rounded-md text-sm font-medium",
  };

  function copyInput(el) {
    $(el).select();
    document.execCommand("copy");
  }

  function showCronModal() {
    const modal_content = `
      <div>
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="mt-3 text-center sm:mt-5">
          <h2 class="text-lg leading-6 font-bold text-gray-900">${_AS.translations.cron_setup_title}</h2>
          <p class="text-xs text-gray-600 mt-2 leading-5">${_AS.translations.cron_setup_description}</p>
          <b>Unix Format: * * * * *</b>
          <div class="mt-4">
            <div>
              <div class="mt-1 relative rounded-md shadow-sm">
                <input type="text" name="cronjob_setup" id="cronjob_setup" class="font-mono focus:ring-as-primary-400 focus:border-as-primary-400 block w-full pr-10 text-xs border-gray-300 rounded-md" value="curl -s ${module_cron_url} > /dev/null 2>&1">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer group cronjob_setup_icon">
                  <svg class="group-hover:stroke-as-primary-400 h-8 w-8 stroke-slate-400 transition" fill="none" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12.9975 10.7499L11.7475 10.7499C10.6429 10.7499 9.74747 11.6453 9.74747 12.7499L9.74747 21.2499C9.74747 22.3544 10.6429 23.2499 11.7475 23.2499L20.2475 23.2499C21.352 23.2499 22.2475 22.3544 22.2475 21.2499L22.2475 12.7499C22.2475 11.6453 21.352 10.7499 20.2475 10.7499L18.9975 10.7499" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M17.9975 12.2499L13.9975 12.2499C13.4452 12.2499 12.9975 11.8022 12.9975 11.2499L12.9975 9.74988C12.9975 9.19759 13.4452 8.74988 13.9975 8.74988L17.9975 8.74988C18.5498 8.74988 18.9975 9.19759 18.9975 9.74988L18.9975 11.2499C18.9975 11.8022 18.5498 12.2499 17.9975 12.2499Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M13.7475 16.2499L18.2475 16.2499" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M13.7475 19.2499L18.2475 19.2499" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><g class="opacity-0"><path d="M15.9975 5.99988L15.9975 3.99988" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M19.9975 5.99988L20.9975 4.99988" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M11.9975 5.99988L10.9975 4.99988" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></g></svg>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-3">
        <span class="text-red-600">${_AS.translations.cron_setup_notice}</span>
      </div>
      <div class="mt-5 sm:mt-6">
        <button type="button" class="dismiss-modal inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-as-primary-400 text-base font-medium text-white hover:bg-as-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-as-primary-400 sm:text-sm">${_AS.translations.got_it}</button>
      </div>
    `;
    AS.helpers.modal(modal_content);
    $("#cronjob_setup, .cronjob_setup_icon").on("click", function () {
      copyInput("#cronjob_setup");
    });
  }

  function showResyncModal() {
    const modal_content = `
      <div class="sm:flex sm:items-start">
        <div class="mt-3 text-center sm:mt-0 sm:text-left mb-4">
          <h3 class="text-lg leading-6 font-medium text-gray-900 modal-title">${_AS.translations.resync_all}</h3>
          <div class="mt-2">
            <p class="text-sm text-gray-500">
              ${_AS.translations.resync_areusure}
              <b>RESYNC</b>
              ${_AS.translations.resync_inthefield}
            </p>
          </div>
        </div>
      </div>
      <div>
        <input type="text" name="confirm_resync" id="confirm_resync" class="shadow-sm focus:ring-as-primary-400 focus:border-as-primary-400 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="RESYNC">
      </div>
      <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
        <button id="resync_all_trigger" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 opacity-25 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled cursor-not-allowed" disabled>${_AS.translations.resync_all}</button>
        <button type="button" class="dismiss-modal mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">${_AS.translations.cancel}</button>
      </div>
    </div>
    `;
    AS.helpers.modal(modal_content);
    $("#confirm_resync").on("keyup", function () {
      const typed = $(this).val();
      if (typed == "RESYNC") {
        $("#resync_all_trigger").removeClass(
          "disabled cursor-not-allowed opacity-25"
        );
        $("#resync_all_trigger").removeAttr("disabled");
      } else {
        $("#resync_all_trigger").addClass(
          "disabled cursor-not-allowed opacity-25"
        );
        $("#resync_all_trigger").attr("disabled", "disabled");
      }
    });
    $("#resync_all_trigger").on("click", function (e) {
      e.preventDefault();
      if ($(this).hasClass("disabled")) return;
      AS.helpers.load("#resync_all_trigger");
      AS.controller("resyncall", "POST")
        .then((r) => {
          AS.helpers.toast(_AS.translations.resync_all_success);
          $(".dismiss-modal").trigger("click");
          location.reload(1);
        })
        .catch((e) => {
          AS.helpers.toast(_AS.translations.resync_all_fail, "ERROR");
          $(".dismiss-modal").trigger("click");
          location.reload(1);
        })
        .finally(() => AS.helpers.unload("#resync_all_trigger"));
    });
  }

  function showDisconnectApikeyModal() {
    const modal_content = `
      <div class="sm:flex sm:items-start">
        <div class="mt-3 text-center sm:mt-0 sm:text-left mb-4">
          <h3 class="text-lg leading-6 font-medium text-gray-900 modal-title">${_AS.translations.disconnect_apikey_title}</h3>
          <div class="mt-2">
            <p class="text-sm text-gray-500">
            ${_AS.translations.disconnect_areusure}
            <b>DISCONNECT</b>
            ${_AS.translations.resync_inthefield}</p>
          </div>
        </div>
      </div>
      <div>
        <input type="text" name="confirm_disconnect" id="confirm_disconnect" class="shadow-sm focus:ring-as-primary-400 focus:border-as-primary-400 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="DISCONNECT">
      </div>
      <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
        <button id="disconnect_all_trigger" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 opacity-25 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled cursor-not-allowed" disabled>${_AS.translations.disconnect_apikey_title}</button>
        <button type="button" class="dismiss-modal mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">${_AS.translations.cancel}</button>
      </div>
    </div>
    `;
    AS.helpers.modal(modal_content);
    $("#confirm_disconnect").on("keyup", function () {
      const typed = $(this).val();
      if (typed == "DISCONNECT") {
        $("#disconnect_all_trigger").removeClass(
          "disabled cursor-not-allowed opacity-25"
        );
        $("#disconnect_all_trigger").removeAttr("disabled");
      } else {
        $("#disconnect_all_trigger").addClass(
          "disabled cursor-not-allowed opacity-25"
        );
        $("#disconnect_all_trigger").attr("disabled", "disabled");
      }
    });
    $("#disconnect_all_trigger").on("click", function (e) {
      e.preventDefault();
      if ($(this).hasClass("disabled")) return;
      AS.helpers.load("#disconnect_all_trigger");
      AS.controller("disconnectapikey", "POST").then((r) => {
        AS.helpers.unload("#disconnect_all_trigger");
        AS.helpers.toast(_AS.translations.disconnect_success);
        $(".dismiss-modal").trigger("click");
        setTimeout(() => {
          location.reload(1);
        }, 3000);
      });
    });
  }

  function updateProductView(products) {
    let productHtml = "";
    $.each(products, function (k, product) {
      const productType =
        k.split("_")[0] == "30" ? "Configurabile" : "Semplice";
      let attrsHtml = "";
      let imagesHtml = "";
      let pricesHtml = "";
      let stocksHtml = "";
      let categoriesHtml = '<p class="ml-2 text-sm text-gray-500">Categorie: ';
      $.each(product.attrs, function (attr_name, attr_value) {
        attrsHtml +=
          '<p class="ml-2 text-sm text-gray-500">' +
          attr_name +
          ": " +
          attr_value +
          "</p>";
      });
      $.each(product.images, function (attr_name, image) {
        imagesHtml += `
        <div class="aspect-w-1 aspect-h-1 rounded-lg overflow-hidden">
          <img src="${image.url}" alt="" class="w-full h-full object-center object-cover">
        </div>
        `;
      });
      $.each(product.prices, function (k, price) {
        pricesHtml += `
          <p class="ml-2 text-sm text-gray-500">
            ${price.currency}, Gruppo: ${price.groupid}| Prezzo: ${price.price} - Prezzo speciale: ${price.specialprice}
          </p>
        `;
      });
      $.each(product.categories, function (k, category) {
        categoriesHtml += `
          ${category.categoryid} -
        `;
      });
      categoriesHtml += "</p>";
      $.each(product.stocks, function (k, stock) {
        stocksHtml += `
          <p class="ml-2 text-sm text-gray-500">Stock: ${stock.quantity}</p>
        `;
      });
      productHtml += `
      <div class="max-w-2xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:max-w-7xl lg:px-8 lg:grid lg:grid-cols-2 lg:gap-x-8">
        <div class="lg:max-w-lg lg:self-end">
          <div class="mt-4">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-gray-100 text-gray-800">${productType}</span>
            <h2 class="text-xl font-extrabold tracking-tight text-gray-900 sm:text-xl">${
              product.attrs.name ?? ""
            }</h2>
          </div>
          <section aria-labelledby="information-heading" class="mt-4">
            <div class="mt-6">
              ${attrsHtml}
              ${pricesHtml}
              ${categoriesHtml}
              ${stocksHtml}
            </div>
          </section>
        </div>
        <div class="mt-10 lg:mt-0 lg:col-start-2 lg:row-span-2 lg:self-center flex">
          ${imagesHtml}
        </div>
        <div>
          <textarea rows="4" class="w-full">${JSON.stringify(
            product
          )}</textarea>
        </div>
      </div>
      `;
    });
    $("#as-product-container").html(productHtml);
  }

  function switchToPage(page) {
    $("#as-menu-nav a").removeClass();
    $("#as-menu-nav a[page='" + page + "']").addClass(menu_classes.selected);
    $("#as-menu-nav a:not(.as-current-page)").addClass(menu_classes.default);
    $("#as-pages .as-page").addClass("as-hidden");
    $("#as-pages [page='" + page + "']").removeClass("as-hidden");
  }

  function renderMissingErrors(missings) {
    let missings_html = "";
    $.each(missings, function (k, v) {
      const [id_shop, id_lang, id_product, id_product_attribute] = v.split("_");
      missings_html += `
      <tr>
        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 md:pl-0">${id_shop}</td>
        <td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500">${id_lang}</td>
        <td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500">${id_product}</td>
        <td class="whitespace-nowrap py-4 px-3 text-sm text-gray-500">${id_product_attribute}</td>
        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 md:pr-0">
          <a href="javascript:AS.helpers.seeLog('${v}')" class="text-indigo-600 hover:text-indigo-900">${_AS.translations.see_log}</a>
        </td>
      </tr>
      `;
    });
    const tables = `
    <div class="px-4 sm:px-6 lg:px-8">
      <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
            <table class="min-w-full divide-y divide-gray-300">
              <thead>
                <tr>
                  <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 md:pl-0">ID Shop</th>
                  <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900">ID Lang</th>
                  <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900">ID Product</th>
                  <th scope="col" class="py-3.5 px-3 text-left text-sm font-semibold text-gray-900">ID Product Attribute</th>
                  <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 md:pr-0">
                    <span class="sr-only">${_AS.translations.see_log}</span>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                ${missings_html}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    `;
    $(".missing-response").html(tables);
  }

  function renderFaqs() {
    AS.controller("getfaqs", "POST").then((r) => {
      if (Object.keys(r.faqs).length == 0) {
        $("#faqs-wrapper").html("No faqs");
      }
      if (Object.keys(r.faqs).length > 0) {
        let faqs_item_count = 0;
        let faqs_section = "";
        $.each(r.faqs, function (section, faq) {
          let faq_single = "";
          $.each(faq, function (answer, response) {
            faq_single += `
            <dl class="mt-6 space-y-6 divide-y divide-gray-200">
              <div class="pt-6">
                <dt class="text-base">
                  <button type="button" class="toggle_faq_item text-left w-full flex justify-between items-start text-gray-400" aria-controls="faq-${faqs_item_count}" aria-expanded="false">
                    <span class="font-medium text-gray-900">${answer}</span>
                    <span class="ml-6 h-7 flex items-center">
                      <svg class="rotate-180 h-6 w-6 transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                      </svg>
                    </span>
                  </button>
                </dt>
                <dd class="mt-4 pr-12" id="faq-${faqs_item_count}" style="display:none">
                  <p class="text-sm text-gray-500">${response}</p>
                </dd>
              </div>
            </dl>
            `;
            faqs_item_count++;
          });
          faqs_section += `
          <div class="mx-auto divide-y-2 divide-gray-200 mb-16">
            <h2 class="text-xl font-bold text-gray-900 sm:text-xl">${section}</h2>
            ${faq_single}
          </div>
          `;
        });
        const faqs_html = `
          <div class="mx-auto py-12 px-4 sm:py-16 sm:px-6 lg:px-8">
            <h2 class="text-xl font-extrabold text-gray-900 sm:text-2xl mb-16">FAQS</h2>
            ${faqs_section}
          </div>
        `;
        $("#faqs-wrapper").html(faqs_html);
        $(".toggle_faq_item").on("click", function () {
          $(this).find("svg").toggleClass("rotate-0 rotate-180");
          $("#" + $(this).attr("aria-controls")).toggle(0);
        });
      }
    });
  }

  $(document).ready(function () {
    renderFaqs();

    $(".start_now").on("click", function () {
      $(".intro").hide(0);
      $(".apikey-insert").show(0);
    });

    if (window.performance) {
      if (
        !!localStorage.getItem("as_lastpage") &&
        performance.navigation.type
      ) {
        switchToPage(localStorage.getItem("as_lastpage"));
      }
    }

    $(".showCronModal").on("click", showCronModal);

    // gestisce la chiusura di tutti gli elementi quando si clicca fuori di essi.
    // Es. i menu quando si clicca fuori di essi
    $(document).mouseup(function (e) {
      const container = $(".as-dropdown");
      if (!container.is(e.target) && container.has(e.target).length === 0) {
        $(".dropdown-container").next().removeClass("as-dropdown-show");
        $(".dropdown-container").next().hide(0);
      }

      if ($(".modal-container").length) {
        const modal_container = $(".modal-content");
        if (
          !modal_container.is(e.target) &&
          modal_container.has(e.target).length === 0
        ) {
          $(".modal-container").remove();
          $("#as-backdrop").hide(0);
        }
      }
    });

    // toggle dei menu dropdown
    $(".dropdown-container > button").on("click", function () {
      $(this).parent().next().toggle(0);
      $(this).parent().next().toggleClass("as-dropdown-show");
    });

    // toggle della nav dashboard.tpl
    $("#as-menu-nav a").on("click", function () {
      const page = $(this).attr("page");
      localStorage.setItem("as_lastpage", page);
      switchToPage(page);
    });

    $("#resync_all_products").on("click", function () {
      showResyncModal();
    });

    $("#as_shop_selection_form").on("submit", function (e) {
      e.preventDefault();
      const shops_selected = [];
      $(".as_shop_to_sync").each(function () {
        if ($(this).prop("checked"))
          shops_selected.push({
            id_shop: $(this).attr("data-id-shop"),
            id_lang: $(this).attr("data-id-lang"),
          });
      });
      if (!shops_selected.length) {
        AS.helpers.toast(_AS.translations.select_at_least_1_shop, "ERROR");
        return false;
      }
      AS.helpers.load("#as_shop_selection_cta");
      AS.controller("addshops", "POST", {
        shops: shops_selected,
      }).then((r) => {
        if (r.success === true) {
          AS.helpers.toast(_AS.translations.shop_sync_success);
          setTimeout(() => {
            location.reload(1);
          }, 3000);
          return;
        }
        AS.helpers.unload("#as_shop_selection_cta");
        AS.helpers.toast(_AS.translations.shop_sync_failed, "ERROR");
      });
    });

    $("#shop_initializations").on("click", function (e) {
      e.preventDefault();
      AS.controller("shopinitializations", "POST").then((r) => {
        console.log(r);
      });
    });

    $("#resync_all_prices").on("click", function (e) {
      e.preventDefault();
      AS.helpers.load("#resync_all_prices");
      AS.controller("resyncallprices", "POST")
        .then((r) => {
          AS.helpers.toast(_AS.translations.resync_price_success);
        })
        .catch((error) =>
          AS.helpers.toast(_AS.translations.resync_price_fail, "ERROR")
        )
        .finally(() => AS.helpers.unload("#resync_all_prices"));
    });

    $("#disconnect_apikey").on("click", function (e) {
      e.preventDefault();
      showDisconnectApikeyModal();
    });

    $("#resync_users_groups").on("click", function (e) {
      e.preventDefault();
      AS.helpers.load("#resync_users_groups");
      AS.controller("resyncusersgroups", "POST").then((r) => {
        AS.helpers.toast(_AS.translations.resync_users_groups_success);
        AS.helpers.unload("#resync_users_groups");
      });
    });

    $("#soft_delete_and_cleanup_products").on("click", function (e) {
      e.preventDefault();
      AS.controller("softdeleteandcleanupproducts", "POST").then((r) => {
        console.log(r);
      });
    });

    $("#start_remote_checker").on("click", function (e) {
      e.preventDefault();
      AS.helpers.load("#start_remote_checker");
      AS.controller("startremotechecker", "POST").then((r) => {
        $("#no-issues, #with-issues").hide(0);
        AS.helpers.unload("#start_remote_checker");
        if (r.missings.length > 0) {
          renderMissingErrors(r.missings);
          $("#with-issues").show(0);
          AS.last_errors = r.errors;
          return;
        }
        $("#no-issues").show(0);
        AS.helpers.toast(_AS.translations.start_remote_checker_success);
      });
    });

    $("#get_as_informations").on("click", function (e) {
      e.preventDefault();
      AS.controller("getasproductinformations", "POST", {
        pid: $("#as_pid").val(),
      }).then((r) => {
        console.log(r);
        if (!Object.keys(r.products)) alert("Nessun prodotto trovato");
        updateProductView(r.products);
      });
    });

    $("#generate_products_query").on("click", function (e) {
      e.preventDefault();
      AS.controller("generateproductsquery", "POST", {
        limit: "0,1000",
      }).then((r) => {
        console.log(r);
      });
    });

    $("#generate_products_queue_query").on("click", function (e) {
      e.preventDefault();
      AS.controller("generateproductsqueuequery", "POST", {
        limit: "0,1000",
      }).then((r) => {
        console.log(r);
      });
    });

    $("#automatic_queue").on("click", function (e) {
      e.preventDefault();
      AS.controller("automaticqueue", "POST").then((r) => {
        console.log(r);
      });
    });

    $("#cleanup_products").on("click", function (e) {
      e.preventDefault();
      AS.controller("cleanupproducts", "POST").then((r) => {
        console.log(r);
      });
    });

    $("#send_queue").on("click", function (e) {
      e.preventDefault();
      AS.controller("sendqueue", "POST").then((r) => {
        console.log(r);
      });
    });

    $("#delete_queue").on("click", function (e) {
      e.preventDefault();
      AS.controller("deletequeue", "POST").then((r) => {
        console.log(r);
      });
    });

    $("#as_apikey_form").on("submit", function (e) {
      e.preventDefault();
      const apikey = $("#apikey").val();
      if (apikey == "") return;
      AS.helpers.load("#as_apikey_cta");
      AS.controller("submitapikey", "POST", {
        apikey: apikey,
      }).then((r) => {
        if (r.success) {
          AS.helpers.toast(_AS.translations.apikey_connection_success);
          setTimeout(() => {
            location.reload(1);
          }, 3000);
          return;
        }
        AS.helpers.unload("#as_apikey_cta");
        AS.helpers.toast(_AS.translations.api_key_error, "ERROR");
      });
    });
  });
})();

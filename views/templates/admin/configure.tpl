<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@200;300;400;500;600;700&display=swap"
    rel="stylesheet">

{foreach from=$css_file_paths item=$css_path}
    <link rel="stylesheet" href="{$css_path}" />
{/foreach}

<style>
    .bootstrap.panel {
        display: none !important;
    }

    .bootstrap a {
        color: inherit;
        text-decoration: inherit;
    }

    .bootstrap a:focus,
    .bootstrap a:hover {
        color: inherit;
        text-decoration: inherit;
    }
</style>

<script>
    /**
     * Translation strings
     */
    const _AST = {
        //START_TRANSLATIONS//
        "Setup cronjob to run sync automatically, it's a mandatory step to complete the onboarding process." : "{l s='Setup cronjob to run sync automatically, it\'s a mandatory step to complete the onboarding process.' mod='accelasearch'}",
        "Once you have setup and executed the cronjob, this screen will be updated automatically and onboarding process will be completed." : "{l s='Once you have setup and executed the cronjob, this screen will be updated automatically and onboarding process will be completed.' mod='accelasearch'}",
        "Copy" : "{l s='Copy' mod='accelasearch'}",
        "Advanced usage" : "{l s='Advanced usage' mod='accelasearch'}",
        "You can run a feed generation command per shop/language directly thanks to CLI commands" : "{l s='You can run a feed generation command per shop/language directly thanks to CLI commands' mod='accelasearch'}",
        "Starting from accelasearch module folder, you can run:" : "{l s='Starting from accelasearch module folder, you can run:' mod='accelasearch'}",
        "Where id_shop and id_lang are the shop and language ids you want to generate." : "{l s='Where id_shop and id_lang are the shop and language ids you want to generate.' mod='accelasearch'}",
        "This let you to replace your cronjob command with a more precise and segmented one valid for data feed generation. It also can be faster depending on your server configuration - shops/languages and products." : "{l s='This let you to replace your cronjob command with a more precise and segmented one valid for data feed generation. It also can be faster depending on your server configuration - shops/languages and products.' mod='accelasearch'}",
        "Settings" : "{l s='Settings' mod='accelasearch'}",
        "Logs" : "{l s='Logs' mod='accelasearch'}",
        "Help" : "{l s='Help' mod='accelasearch'}",
        "Cronjob" : "{l s='Cronjob' mod='accelasearch'}",
        "Cerca per sku, nome o brand..." : "{l s='Cerca per sku, nome o brand...' mod='accelasearch'}",
        "Pulisci" : "{l s='Pulisci' mod='accelasearch'}",
        "Are you sure you want to disconnect? You will be redirected to welcome page and lost all configurations from Prestashop side." : "{l s='Are you sure you want to disconnect? You will be redirected to welcome page and lost all configurations from Prestashop side.' mod='accelasearch'}",
        "Disconnecting..." : "{l s='Disconnecting...' mod='accelasearch'}",
        "Disconnected successfully" : "{l s='Disconnected successfully' mod='accelasearch'}",
        "An error occurred during disconnect." : "{l s='An error occurred during disconnect.' mod='accelasearch'}",
        "Disconnect" : "{l s='Disconnect' mod='accelasearch'}",
        "Updating module..." : "{l s='Updating module...' mod='accelasearch'}",
        "Module updated successfully." : "{l s='Module updated successfully.' mod='accelasearch'}",
        "Failed to update module." : "{l s='Failed to update module.' mod='accelasearch'}",
        "Unlocking and sending report..." : "{l s='Unlocking and sending report...' mod='accelasearch'}",
        "Cronjob unlocked and report sent successfully." : "{l s='Cronjob unlocked and report sent successfully.' mod='accelasearch'}",
        "Failed to unlock and send report." : "{l s='Failed to unlock and send report.' mod='accelasearch'}",
        "A new version of module is available, update now to don't miss latest features." : "{l s='A new version of module is available, update now to don\'t miss latest features.' mod='accelasearch'}",
        "Updating module, wait..." : "{l s='Updating module, wait...' mod='accelasearch'}",
        "Update now" : "{l s='Update now' mod='accelasearch'}",
        "Some cronjobs are locked for more than 60 minutes, usually this is caused by a fatal error during the executions, please check your error logs and system logs from Tab below for more details. You can also unlock and send a report to our support team." : "{l s='Some cronjobs are locked for more than 60 minutes, usually this is caused by a fatal error during the executions, please check your error logs and system logs from Tab below for more details. You can also unlock and send a report to our support team.' mod='accelasearch'}",
        "Unlock and sending, wait..." : "{l s='Unlock and sending, wait...' mod='accelasearch'}",
        "Unlock and send report" : "{l s='Unlock and send report' mod='accelasearch'}",
        "Adding shops to sync..." : "{l s='Adding shops to sync...' mod='accelasearch'}",
        "Shops selected successfully" : "{l s='Shops selected successfully' mod='accelasearch'}",
        "Select the shops/languages you want to sync on AccelaSearch" : "{l s='Select the shops/languages you want to sync on AccelaSearch' mod='accelasearch'}",
        "An error occurred during load your shops." : "{l s='An error occurred during load your shops.' mod='accelasearch'}",
        "Loading..." : "{l s='Loading...' mod='accelasearch'}",
        "Synchronize" : "{l s='Synchronize' mod='accelasearch'}",
        "Update" : "{l s='Update' mod='accelasearch'}",
        "shops to Accelasearch" : "{l s='shops to Accelasearch' mod='accelasearch'}",
        "Saving..." : "{l s='Saving...' mod='accelasearch'}",
        "Saved" : "{l s='Saved' mod='accelasearch'}",
        "Error" : "{l s='Error' mod='accelasearch'}",
        "Select your color attribute" : "{l s='Select your color attribute' mod='accelasearch'}",
        "Don't sync" : "{l s='Don\'t sync' mod='accelasearch'}",
        "Select your size attribute" : "{l s='Select your size attribute' mod='accelasearch'}",
        "Select your synchronization type" : "{l s='Select your synchronization type' mod='accelasearch'}",
        "Map your existing attributes to AccelaSearch attributes color/size, if you don't want to manage these attributes select the option 'Don't sync'" : "{l s='Map your existing attributes to AccelaSearch attributes color/size, if you don\'t want to manage these attributes select the option \'Don\'t sync\'' mod='accelasearch'}",
        "Go to cronjob configuration" : "{l s='Go to cronjob configuration' mod='accelasearch'}",
        "Both configurable and simple products" : "{l s='Both configurable and simple products' mod='accelasearch'}",
        "Send to Accelasearch both configurable and simple products, simple products are displayed as variants of configurable products. This option only works for size and color attributes." : "{l s='Send to Accelasearch both configurable and simple products, simple products are displayed as variants of configurable products. This option only works for size and color attributes.' mod='accelasearch'}",
        "Only configurable products" : "{l s='Only configurable products' mod='accelasearch'}",
        "Send to Accelasearch only configurable products, variants of products will be ignored." : "{l s='Send to Accelasearch only configurable products, variants of products will be ignored.' mod='accelasearch'}",
        "Only simple products" : "{l s='Only simple products' mod='accelasearch'}",
        "Send to Accelasearch only simple products, only each variant of products will be sent (this can be increase your product numbers)." : "{l s='Send to Accelasearch only simple products, only each variant of products will be sent (this can be increase your product numbers).' mod='accelasearch'}",
        "What is Accelasearch?" : "{l s='What is Accelasearch?' mod='accelasearch'}",
        "Accelasearch is an AI-powered search engine solution for e-commerce websites." : "{l s='Accelasearch is an AI-powered search engine solution for e-commerce websites.' mod='accelasearch'}",
        "How can I integrate Accelasearch with my PrestaShop store?" : "{l s='How can I integrate Accelasearch with my PrestaShop store?' mod='accelasearch'}",
        "You can integrate Accelasearch with your PrestaShop store by installing the provided module." : "{l s='You can integrate Accelasearch with your PrestaShop store by installing the provided module.' mod='accelasearch'}",
        "Where can I configure and customize Accelasearch for my e-commerce site?" : "{l s='Where can I configure and customize Accelasearch for my e-commerce site?' mod='accelasearch'}",
        "You can configure and customize Accelasearch on the dedicated dashboard at accelasearch.io." : "{l s='You can configure and customize Accelasearch on the dedicated dashboard at accelasearch.io.' mod='accelasearch'}",
        "What configuration options are available on the Accelasearch dashboard?" : "{l s='What configuration options are available on the Accelasearch dashboard?' mod='accelasearch'}",
        "The dashboard allows you to customize filters, search appearance, add variants, change product sorting, and include banners." : "{l s='The dashboard allows you to customize filters, search appearance, add variants, change product sorting, and include banners.' mod='accelasearch'}",
        "How does the onboarding process work with Accelasearch?" : "{l s='How does the onboarding process work with Accelasearch?' mod='accelasearch'}",
        "The onboarding process involves setting up your preferences and configurations on the dashboard to tailor the search engine to your e-commerce needs." : "{l s='The onboarding process involves setting up your preferences and configurations on the dashboard to tailor the search engine to your e-commerce needs.' mod='accelasearch'}",
        "Can I adjust the search results' order and relevance?" : "{l s='Can I adjust the search results\' order and relevance?' mod='accelasearch'}",
        "Yes, you can customize and fine-tune the order and relevance of search results according to your preferences." : "{l s='Yes, you can customize and fine-tune the order and relevance of search results according to your preferences.' mod='accelasearch'}",
        "Are there options to sync my products automatically with Accelasearch?" : "{l s='Are there options to sync my products automatically with Accelasearch?' mod='accelasearch'}",
        "After completing onboarding, the PrestaShop module provides the option to configure automatic product synchronization." : "{l s='After completing onboarding, the PrestaShop module provides the option to configure automatic product synchronization.' mod='accelasearch'}",
        "What benefits does Accelasearch offer for e-commerce businesses?" : "{l s='What benefits does Accelasearch offer for e-commerce businesses?' mod='accelasearch'}",
        "Accelasearch helps improve the user experience on your e-commerce site by providing more relevant and efficient search results." : "{l s='Accelasearch helps improve the user experience on your e-commerce site by providing more relevant and efficient search results.' mod='accelasearch'}",
        "Can I integrate Accelasearch with other e-commerce platforms besides PrestaShop?" : "{l s='Can I integrate Accelasearch with other e-commerce platforms besides PrestaShop?' mod='accelasearch'}",
        "Accelasearch may be integrated with other e-commerce platforms. Contact our support for more information." : "{l s='Accelasearch may be integrated with other e-commerce platforms. Contact our support for more information.' mod='accelasearch'}",
        "How do I get started with Accelasearch and PrestaShop integration?" : "{l s='How do I get started with Accelasearch and PrestaShop integration?' mod='accelasearch'}",
        "Begin by installing the Accelasearch module for PrestaShop, and then proceed to set up your preferences and configurations on the Accelasearch dashboard for a tailored search experience on your e-commerce site." : "{l s='Begin by installing the Accelasearch module for PrestaShop, and then proceed to set up your preferences and configurations on the Accelasearch dashboard for a tailored search experience on your e-commerce site.' mod='accelasearch'}",
        "Cronjob setup" : "{l s='Cronjob setup' mod='accelasearch'}",
        "Attention to Cronjob Token" : "{l s='Attention to Cronjob Token' mod='accelasearch'}",
        "Cronjob token will change on disconnect and will be regenerated, don't forget to update your cronjob command." : "{l s='Cronjob token will change on disconnect and will be regenerated, don\'t forget to update your cronjob command.' mod='accelasearch'}",
        "What can you do with this module?" : "{l s='What can you do with this module?' mod='accelasearch'}",
        "Relevant Search Results: Accelasearch delivers highly relevant search results" : "{l s='Relevant Search Results: Accelasearch delivers highly relevant search results' mod='accelasearch'}",
        "Enhanced User Experience: Advanced search features make online shopping more efficient." : "{l s='Enhanced User Experience: Advanced search features make online shopping more efficient.' mod='accelasearch'}",
        "Increased Sales: Accurate results and personalized recommendations can boost sales." : "{l s='Increased Sales: Accurate results and personalized recommendations can boost sales.' mod='accelasearch'}",
        "Customization: Businesses can tailor the search experience to their brand and products." : "{l s='Customization: Businesses can tailor the search experience to their brand and products.' mod='accelasearch'}",
        "Cannot find a solution to your issue?" : "{l s='Cannot find a solution to your issue?' mod='accelasearch'}",
        "Open a support ticket" : "{l s='Open a support ticket' mod='accelasearch'}",
        "Frequently asked questions" : "{l s='Frequently asked questions' mod='accelasearch'}",
        "Check the system logs" : "{l s='Check the system logs' mod='accelasearch'}",
        "Shop selection" : "{l s='Shop selection' mod='accelasearch'}",
        "Sync type & Attributes" : "{l s='Sync type & Attributes' mod='accelasearch'}",
        "Cronjob configuration" : "{l s='Cronjob configuration' mod='accelasearch'}",
        "If you go back you lost current changes, are you sure?" : "{l s='If you go back you lost current changes, are you sure?' mod='accelasearch'}",
        "Last cronjob execution time" : "{l s='Last cronjob execution time' mod='accelasearch'}",
        "Shops/Languages synced" : "{l s='Shops/Languages synced' mod='accelasearch'}",
        "Search Layer" : "{l s='Search Layer' mod='accelasearch'}",
        "Configure your search layer selectors to start using accelasearch" : "{l s='Configure your search layer selectors to start using accelasearch' mod='accelasearch'}",
        "Go to accelasearch console →" : "{l s='Go to accelasearch console →' mod='accelasearch'}",
        "Verifying ApiKey..." : "{l s='Verifying ApiKey...' mod='accelasearch'}",
        "Your ApiKey is valid! Redirecting..." : "{l s='Your ApiKey is valid! Redirecting...' mod='accelasearch'}",
        "ApiKey not valid!" : "{l s='ApiKey not valid!' mod='accelasearch'}",
        "Please insert a valid ApiKey" : "{l s='Please insert a valid ApiKey' mod='accelasearch'}",
        "Link your account" : "{l s='Link your account' mod='accelasearch'}",
        "Copy your Api Key from AccelaSearch console and paste it below" : "{l s='Copy your Api Key from AccelaSearch console and paste it below' mod='accelasearch'}",
        "ApiKey" : "{l s='ApiKey' mod='accelasearch'}",
        "Link to Accelasearch" : "{l s='Link to Accelasearch' mod='accelasearch'}",
        "Do you need an ApiKey? Try AccelaSearch for free for 30 days" : "{l s='Do you need an ApiKey? Try AccelaSearch for free for 30 days' mod='accelasearch'}",
        "Boost your search engine without knowing one line code" : "{l s='Boost your search engine without knowing one line code' mod='accelasearch'}",
        "Start now!" : "{l s='Start now!' mod='accelasearch'}",
        "No credit card required" : "{l s='No credit card required' mod='accelasearch'}",
        "No coding skill required" : "{l s='No coding skill required' mod='accelasearch'}",
        "Easy to configure" : "{l s='Easy to configure' mod='accelasearch'}",
        "AI Search Engine to show search results never seen before" : "{l s='AI Search Engine to show search results never seen before' mod='accelasearch'}",
        "Giving your users the ability to find what they are looking for in a much simpler and AI-powered way means increasing the value of your products through faster and more relevant searches and results." : "{l s='Giving your users the ability to find what they are looking for in a much simpler and AI-powered way means increasing the value of your products through faster and more relevant searches and results.' mod='accelasearch'}",
        "Collect valuable information from your users every day" : "{l s='Collect valuable information from your users every day' mod='accelasearch'}",
        "Learn from your users' behavior. Get to know their most searched and clicked products in a chosen time period. Learn more about your products and get all the information you were missing." : "{l s='Learn from your users\' behavior. Get to know their most searched and clicked products in a chosen time period. Learn more about your products and get all the information you were missing.' mod='accelasearch'}",
        "Create visual experiences without the use of code" : "{l s='Create visual experiences without the use of code' mod='accelasearch'}",
        "The No-code revolution has taken over and customizing your tools is more important than ever. AccelaSearch allows you to customize your search engine as you wish without relying on developers." : "{l s='The No-code revolution has taken over and customizing your tools is more important than ever. AccelaSearch allows you to customize your search engine as you wish without relying on developers.' mod='accelasearch'}",
        "Start using AccelaSearch now!" : "{l s='Start using AccelaSearch now!' mod='accelasearch'}",
        //END_TRANSLATIONS// 
    }
</script>

<div id="accelasearch-app" style="margin: -15px -10px -15px -15px">
</div>


{foreach from=$js_file_paths item=$js_path}
    <script src="{$js_path}"></script>
{/foreach}
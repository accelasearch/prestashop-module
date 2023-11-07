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
        "Welcome to Accelasearch!" : "{l s='Welcome to Accelasearch!' mod='accelasearch'}",
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
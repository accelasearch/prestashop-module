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
</style>

<script>
    /**
     * Translation strings
     */
    const _AST = {
        //START_TRANSLATIONS//
        "Submit" : "{l s='Submit' mod='accelasearch'}",
        "Inexistent translation" : "{l s='Inexistent translation' mod='accelasearch'}",
        "Another translation" : "{l s='Another translation' mod='accelasearch'}",
        //END_TRANSLATIONS//
    }
</script>

<div id="accelasearch-app">
</div>


{foreach from=$js_file_paths item=$js_path}
    <script src="{$js_path}"></script>
{/foreach}
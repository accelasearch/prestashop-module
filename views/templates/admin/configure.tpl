{foreach from=$css_file_paths item=$css_path}
    <link rel="stylesheet" href="{$css_path}" />
{/foreach}

<div id="accelasearch-app">
</div>


{foreach from=$js_file_paths item=$js_path}
    <script src="{$js_path}"></script>
{/foreach}
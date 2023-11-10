<?php

// helper script to create translation words from .jsx files to .tpl files to let prestashop scan them

require_once __DIR__ . "/../../../config/config.inc.php";

$translation_file = __DIR__ . "/../translations/it.php";
$regex = "/\$_MODULE\['<\{accelasearch\}prestashop>configure_'\] = (.*)/";

$translation = file_get_contents($translation_file);
preg_match_all('/\$_MODULE\[\'<\{accelasearch\}prestashop>configure_\'\] = \'(.*)\'/', $translation, $matches);

foreach ($matches[0] as $k => $phrase) {
    $md5 = md5($matches[1][$k]);
    $newPhrase = "\$_MODULE['<{accelasearch}prestashop>configure_$md5'] = '" . $matches[1][$k] . "'";
    $translation = str_replace($phrase, $newPhrase, $translation);
}

file_put_contents($translation_file, $translation);

echo "Done\n";
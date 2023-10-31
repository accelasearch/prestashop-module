<?php

// helper script to create translation words from .jsx files to .tpl files to let prestashop scan them

require_once __DIR__ . "/../../config/config.inc.php";

$react_dir = __DIR__ . "/react/src/";
$configuration_file = __DIR__ . "/views/templates/admin/configure.tpl";

use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder->files()->contains('/\{t\("([^"]+)"\)}/')->in($react_dir)->name('*.jsx');

$words = [
    "//START_TRANSLATIONS//",
];

foreach ($finder as $file) {
    $content = $file->getContents();
    preg_match_all('/\{t\("([^"]+)"\)}/', $content, $matches);
    foreach ($matches[1] as $match) {
        $words[] = '"' . $match . '" : "{l s=\'' . $match . '\' mod=\'accelasearch\'}",';
    }
}

$config = file_get_contents($configuration_file);
$words = array_unique($words);

$words[] = "//END_TRANSLATIONS//";

$words = implode("\n", $words);
$config = preg_replace('/\/\/START_TRANSLATIONS\/\/(.*?)\/\/END_TRANSLATIONS\/\//s', $words, $config);

file_put_contents($configuration_file, $config);
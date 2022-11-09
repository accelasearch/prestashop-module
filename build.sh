#!/usr/bin/bash

mkdir temp
mkdir temp/accelasearch

cp -R ./classes temp/accelasearch
cp -R ./controllers temp/accelasearch
cp -R ./sql temp/accelasearch
cp -R ./views temp/accelasearch
cp -R ./README.MD temp/accelasearch
cp -R ./accelasearch.php temp/accelasearch && sed -i 's/"DEBUG_MODE" => true/"DEBUG_MODE" => false/g' temp/accelasearch/accelasearch.php
cp -R ./cron.php temp/accelasearch
cp -R ./autoload.php temp/accelasearch
cp -R ./logo.png temp/accelasearch
cp -R ./sample_structure.png temp/accelasearch

# some production files adjustments
rm -rf temp/accelasearch/classes/_dev
rm -rf temp/accelasearch/classes/Updater/updater_uml.png
rm -rf temp/accelasearch/README.MD
rm -rf temp/accelasearch/sample_structure.png

# remove php comments
find temp/accelasearch -type f -name "*.php" | while read file; do sed -i -e '/\/\*/,/*\//d; /^[[:space:]]*\/\//d; /^$/d;' $file;done

rm -rf ./releases/accelasearch.zip
cd temp && zip -rq ../releases/accelasearch.zip accelasearch && cd ..
rm -rf ./temp

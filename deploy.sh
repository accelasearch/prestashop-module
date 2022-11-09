#!/usr/bin/bash

HOSTS=('85.10.201.172' '185.56.218.169')
USERS=('as_dev' 'as_dev@bimbimatti.it')
PASSWDS=('Zslq7&175' 'MP3nSA2gFveu')

for i in "${!HOSTS[@]}"
do
ftp -in ${HOSTS[$i]} > /dev/null 2>&1 <<END_SCRIPT
  quote USER ${USERS[$i]}
  quote PASS ${PASSWDS[$i]}
  binary
  mkdir classes
  mkdir classes/Updater
  mkdir classes/_dev
  mkdir classes/_dev/src
  mkdir controllers
  mkdir controllers/admin
  mkdir sql
  mkdir views
  mkdir views/css
  mkdir views/img
  mkdir views/js
  mkdir views/templates
  mkdir views/templates/admin
  mput README.MD
  mput autoload.php
  mput accelasearch.php
  mput sample_structure.png
  mput cron.php
  mput logo.png
  mput classes/*
  mput classes/Updater/*
  mput classes/_dev/src/*
  mput controllers/admin/*
  mput sql/*
  mput views/css/*
  mput views/img/*
  mput views/js/*
  mput views/templates/admin/*
  quit
END_SCRIPT
done

exit 0

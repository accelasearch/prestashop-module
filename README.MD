# Avvio dell'environment di sviluppo

Se si vuole cambiare versione di PS o aggiungerne di nuove ed avviarle contemporaneamente basta modificare il docker-compose ed aggiornare le proprie env variables.

    docker-compose-up -d

Accesso backend: /adminPS

Credenziali: demo@prestashop.com - prestashop_demo

## Running tests

Step preliminare: **composer install** sulla root del modulo

### PHP-CS FIXER CODING STANDARD

    php vendor/bin/php-cs-fixer fix --dry-run

### LICENSE HEADERS

    php vendor/bin/header-stamp --license=vendor/prestashop/header-stamp/assets/afl.txt --exclude=vendor,tests,_dev

### PHP STAN

    _PS_ROOT_DIR_=/var/www/html vendor/bin/phpstan analyse --configuration=tests/phpstan/phpstan.neon

### UNIT TESTS

    php vendor/bin/phpunit tests

## Traduzioni

Gestione delle traduzioni nei file .js

## Come aggiungere nuove frasi e passarle al contesto Javascript

Modificare la classe

    classes/translator.php

e leggere dall'oggetto JS **_AS.translations**

Per tradurre fare riferimento al sistema di traduzione nativo di Prestashop

## Modalità DEBUG

Per attivare la modalità debug impostare il flag **DEBUG_MODE** dentro

    accelasearch.php

a **true**.

La modalità debug mostra dei comandi aggiuntivi nella sezione advanced per debuggare le informazioni dei prodotti o triggerare eventi specifici.

## Log Query

Per attivare il log delle query impostare il flag **LOG_QUERY** dentro

    accelasearch.php

a **true**.

Le query verranno loggate dentro i log nativi di Prestashop della tabella ps_log.

## Sync differenziale

Le classi presenti src/Updater/* si occupano della sync differenziale, il pattern usato è Strategy in funzione del tipo di aggiornamento vengono performate le corrispondenti azioni in caso di "update/create/delete".

## TODO

⬜️ Scrivere Unit Tests

⬜️ Refactoring entrypoint modulo ed aggiungere una classe Core separata

⬜️ Aggiungere il supporto al prodotto bundle e virtuale

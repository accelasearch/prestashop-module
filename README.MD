# External id formats

immagini

`id esterno dell'immagine => externalidstr => %id_shop%_%id_lang%_%id_product%_%id_product_attribute%_%id_image%_%image_type%`

`id esterno del prodotto => externalproductidstr => %id_shop%_%id_lang%_%id_product%_%id_product_attribute%`

# Traduzioni

Gestione delle traduzioni nei file .js

## Come aggiungere nuove frasi e passarle al contesto Javascript

Modificare la classe

    classes/translator.php

e leggere dall'oggetto **_AS.translations**

Per tradurre fare riferimento al sistema di traduzione nativo di Prestashop

# Modalità DEBUG

Per attivare la modalità debug impostare il flag **DEBUG_MODE** dentro

    accelasearch.php

a **true**.

La modalità debug mostra dei comandi aggiuntivi nella sezione advanced per debuggare le informazioni dei prodotti o triggerare eventi specifici.

# Log Query

Per attivare il log delle query impostare il flag **LOG_QUERY** dentro

    accelasearch.php

a **true**.

Le query verranno loggate dentro i log nativi di Prestashop della tabella ps_log.

# Sync differenziale

Le classi presenti dentro

    classes/trigger.php
    classes/trigger.data.php

si occupano rispettivamente di creare i Trigger SQL per gli aggiornamenti differenziali secondo la definizione dello schema presente dentro trigger.data.php.

L'array definito dentro TriggerDataElements viene passato alla classe Trigger che fa da handler per la generazione effettiva delle query SQL per la generazione dei triggers.

La coda degli aggiornamenti (as_notifications) viene letta dal cronjob (o dal trigger pageview temporizzato), viene pulita, ordinata e compattata (eliminando aggiornamenti che a livello logico sarebbero inutili, per es. aggiornamento multipli di prezzo - viene letto solo l'ultimo valore utile della coda) - questo avviene tramite costruzione di un array multidimensionale che segue questa scaletta logica nella maggior parte dei casi.


    id_product
        id_product_attribute
            entity_type
                operation_type
                    name

Di questo processo se ne occupa <span style="color:#FDDA16">AccelaSearch::orderAndFilterRows</span>

Ci sono alcuni aggiornamenti che non possono essere soggetti a questa logica, nello specifico:

#### attribute_image

Non può essere soggetto a questo tipo di logica perchè Prestashop ogni volta che un prodotto viene aggiornato, fa un delete ed un re-insert nella tabella product_attribute_image degli id_image correlati agli id_product_attribute e per poter essere interpretati in modo corretto, queste operazioni devono essere eseguite "a cascata" nello stesso ordine con cui sono stati inseriti nella tabella as_notifications.

Per aggirare questo problema viene modificata leggermente la struttura della cascata modificando

    id_product

con

    id_row_notifications_id_image

#### category_product e image

Lo stesso problema avviene con la cascata di aggiornamenti delle categorie assegnate ad un prodotto. Anche in questo caso devono essere eseguiti in ordine cronologico e non possono sovrascriversi i valori a vicenda.

In questo caso l'implementazione è stata fatta in modo differente agendo a livello di

    name

modificandolo in

    name_value

Per approfondire si può fare riferimento direttamente al codice.

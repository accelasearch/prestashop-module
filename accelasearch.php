<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use AccelaSearch\Collector;
use AccelaSearch\Query\Query;
use AccelaSearch\Query\QueryData;
use AccelaSearch\Queue;
use AccelaSearch\Trigger\Trigger;
use AccelaSearch\Trigger\TriggerData;
use AccelaSearch\Trigger\TriggerDataElements;

class AccelaSearch extends Module
{
    public static $as_shops_synced = null;
    public static $as_categories = null;
    const DEFAULT_CONFIGURATION = [
        'ACCELASEARCH_APIKEY' => '',
        'ACCELASEARCH_COLLECTOR' => '',
        'ACCELASEARCH_SHOPS_SYNCED' => '{}',
        'ACCELASEARCH_CRON_TOKEN' => '',
        'ACCELASEARCH_LAST_CRONJOB_EXECUTION' => 0,
        'ACCELASEARCH_LAST_CRONJOB_PAGEVIEW_EXECUTION' => 0,
        'ACCELASEARCH_CRONJOB_PAGEVIEW_EXECUTION_TIMES' => 0,
        'ACCELASEARCH_FULLSYNC_CREATION_PROGRESS' => 0,
    ];

    const TABLE_KEYS = [
        'products',
        'prices',
        'stocks',
        'products_children',
        'products_images',
        'categories',
        'products_attr_text',
        'products_attr_str',
        'products_attr_int',
        'products_attr_float',
        'products_attr_datetime',
    ];

    const WITHOUT_IGNORE = false;

    const AS_CONFIG = [
        'API_ENDPOINT' => 'https://svc11.accelasearch.net/API/',
        'CMS_ID' => 99,
        'LOG_QUERY' => false,
        'DEBUG_MODE' => true,
        'CRONJOB_DRYRUN' => false,
        'ACCEPTED_METHODS' => [
            'GET',
            'POST',
            'DELETE',
        ],
    ];

    const DELETABLE_TABLES = [
        'categories',
        'products',
        'stocks',
        'prices',
        'products_images',
        'products_images_lbl',
        'products_children',
        'products_attr_int',
        'products_attr_str',
        'products_attr_text',
        'products_attr_datetime',
        'products_attr_float',
        'products_attr_label',
        'products_categories',
    ];

    const HOOKS = [
        'actionAdminControllerSetMedia',
        'actionFrontControllerSetMedia',
        'actionCronJob',
        'actionControllerInitBefore',
    ];

    private function initializeModule()
    {
        $this->name = 'accelasearch';
        $this->tab = 'front_office_features';
        $this->version = '0.0.122';
        $this->author = 'AccelaSearch';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('AccelaSearch');
        $this->description = $this->l('Boost your search engine');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module ?');
    }

    public static function generateToken($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public static function asApi(
        string $controller = '',
        string $method = 'GET',
        array $data = [],
        bool $is_auth = false,
        array $headers = []
    ) {
        $method = strtoupper($method);
        if ($is_auth && empty(Configuration::get('ACCELASEARCH_APIKEY'))) {
            throw new \Exception('Cannot send API request without any configured ApiKey');
        }
        if (!in_array($method, self::AS_CONFIG['ACCEPTED_METHODS'])) {
            throw new \Exception('Invalid method');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::AS_CONFIG['API_ENDPOINT'] . $controller);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if ($is_auth) {
            $headers[] = 'X-Accelasearch-Apikey: ' . Configuration::get('ACCELASEARCH_APIKEY');
        }
        if (count($data) > 0) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        if (count($headers) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public static function triggerCronjobExternal()
    {
        $url = Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . 'modules/accelasearch/cron.php?token=' . Configuration::get('ACCELASEARCH_CRON_TOKEN') . '&wait=false&origin=pageview';
        @Tools::file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 0.1]]));
    }

    public static function convertShopIdFromCollectorVersionToReal($id)
    {
        $conversion = self::asApi(
            'shops/' . $id . '/convert',
            'GET',
            [],
            true
        );
        $conversion = json_decode($conversion);
        $status = $conversion->status ?? null;
        if ($status === 'ERROR') {
            Db::getInstance()->insert('log', [
                'severity' => 3,
                'error_code' => 0,
                'message' => 'An error occured during shop conversion to real: ' . json_encode($conversion),
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s'),
            ]);
            throw new \Exception('An error occured during shop conversion to real');
        }

        return $conversion->shopIdentifier;
    }

    public static function notifyShops()
    {
        $notify = self::asApi(
            'shops/notify',
            'POST',
            [],
            true
        );
        $notify = json_decode($notify);
        $status = $notify->status ?? null;
        if ($status === 'ERROR') {
            Db::getInstance()->insert('log', [
                'severity' => 3,
                'error_code' => 0,
                'message' => 'An error occured during shop notification to AccelaSearch: ' . json_encode($notify),
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s'),
            ]);
            throw new \Exception('An error occured during shop notification to AccelaSearch');
        }

        return $notify;
    }

    public static function getCategoriesByIdShopAndLang($id_shop, $id_lang)
    {
        $raw_query = Query::getByName('getCategoriesByIdShopAndLang_query', [
            'id_shop' => $id_shop,
            'id_lang' => $id_lang,
        ]);

        return Db::getInstance()->executeS($raw_query);
    }

    public static function getFullCategoryNameByIdAndLang($id_category, $id_lang)
    {
        $category_tree = [];
        $id_parent = 0;
        $executions = 0;
        while ($id_category > 2) {
            $raw_query = Query::getByName('getFullCategoryNameByIdAndLang_query', [
                'id_lang' => $id_lang,
                'id_category' => $id_category,
            ]);
            $category = Db::getInstance()->getRow($raw_query);
            $id_parent = $category['id_parent'];
            $id_category = $id_parent;
            $category_tree[] = $category['name'];
            if ($executions >= 5) {
                break;
            }
            ++$executions;
        }

        return implode(' / ', array_reverse($category_tree));
    }

    public static function getCategoryById($id, $categories)
    {
        foreach ($categories as $category) {
            if ($category['id_category'] == $id) {
                return $category;
            }
        }

        return false;
    }

    public static function parseCategories($categories, $id_shop, $id_lang, $storeview_id, &$processed_parents = [])
    {
        if (!(bool) count($categories)) {
            return;
        }
        $id_parent = 0;
        foreach ($categories as $category) {
            $link = new Link();
            $children_exist = (isset($category['children'])) ? true : false;
            $id_category = $category['id_category'];
            $id_parent_category = $category['id_parent'];
            if (in_array($id_parent_category, array_keys($processed_parents))) {
                $id_parent = $processed_parents[$id_parent_category];
            }
            $name = $category['name'];
            $full_name = self::getFullCategoryNameByIdAndLang($id_category, $id_lang);
            $url = $link->getCategoryLink($id_category, null, $id_lang, null, $id_shop);
            $external_id_str = $id_shop . '_' . $id_lang . '_' . $id_category;

            $generated_id = Collector::getInstance()->insert('categories', [
                'storeviewid' => $storeview_id,
                'categoryname' => $name,
                'fullcategoryname' => $full_name,
                'externalidstr' => $external_id_str,
                'url' => $url,
                'parentid' => $id_parent,
            ], true);

            if (isset($category['children'])) {
                $processed_parents[$id_category] = $generated_id;
            }

            if ($children_exist) {
                self::parseCategories($category['children'], $id_shop, $id_lang, $storeview_id, $processed_parents);
            }
        }
    }

    public static function sanitize($str)
    {
        $str = str_replace([
            'á', 'à', 'â', 'ã', 'ä', 'å', 'ç', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ñ', 'ó',
            'ò', 'ô', 'õ', 'ö', 'ú', 'ù', 'û', 'ü', 'Ã',
        ], [
            'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'a',
        ], $str);

        return Tools::safeOutput($str);
    }

    public static function generateCategories()
    {
        $as_shops = self::getAsShops();
        foreach ($as_shops as $id_shop_and_lang => $as_shop) {
            $storeview_id = $as_shop['as_shop_id'];
            $id_shop = $as_shop['id_shop'];
            $id_lang = $as_shop['id_lang'];
            Shop::setContext(Shop::CONTEXT_SHOP, $id_shop);
            $full_categories = Category::getNestedCategories(1, $id_lang);
            self::parseCategories($full_categories, $id_shop, $id_lang, $storeview_id);
        }
        Shop::setContext(Shop::CONTEXT_ALL);
    }

    public static function sqlSlug($name)
    {
        // Convert all non A-Z characters to _
        $name = preg_replace('/[^A-Za-z0-9]/', '_', $name);

        return $name;
    }

    public static function generateVariantsQuery($storeview_id, $id_lang)
    {
        $attributes = AttributeGroup::getAttributesGroups($id_lang);
        $queries = [];
        foreach ($attributes as $attribute) {
            $slug = self::sqlSlug($attribute['name']);
            $external_id_str = 'attribute_0_' . $id_lang . '_' . $attribute['id_attribute_group'];
            $queries[] = Query::getByName(
                'createVariant_query',
                [
                    'external_id_str' => $external_id_str,
                    'name' => self::sanitize($attribute['name']),
                    'storeview_id' => $storeview_id,
                    'slug' => $slug,
                ]
            );
        }

        return implode('', $queries);
    }

    // Convert all non letter and non number characters to underscore
    public static function slugify($str)
    {
        return preg_replace('/[^A-Za-z0-9-]+/', '_', $str);
    }

    public static function generateFeaturesQuery($storeview_id, $id_lang)
    {
        $features = Feature::getFeatures($id_lang);
        $queries = [];
        foreach ($features as $feature) {
            $slug = strtolower(str_replace([' ', '-'], ['_', '_'], self::slugify($feature['name'])));
            $external_id_str = 'feature_0_' . $id_lang . '_' . $feature['id_feature'];
            $queries[] = Query::getByName(
                'createVariant_query',
                [
                    'external_id_str' => $external_id_str,
                    'name' => self::sanitize($feature['name']),
                    'storeview_id' => $storeview_id,
                    'slug' => $slug,
                ]
            );
        }

        return implode('', $queries);
    }

    /**
     * Attributi da aggiungere su AS:
     * name, short_description, description, brand, ean13, isbn, upc, mpn, sku
     *
     * @return bool
     */
    public static function shopInitializations()
    {
        $as_shops = self::getAsShops();
        $queries = [];
        $link = new Link();
        foreach ($as_shops as $id_shop_and_lang => $as_shop) {
            $storeview_id = $as_shop['as_shop_id'];
            $id_shop = $as_shop['id_shop'];
            $id_lang = $as_shop['id_lang'];
            $externalidstr_warehouse = $id_shop . '_' . $id_lang;

            $queries[] = 'DELETE FROM products_attr_label WHERE storeviewid ="' . $storeview_id . '";';
            $queries[] = 'DELETE FROM users_groups WHERE storeviewid ="' . $storeview_id . '";';
            $queries[] = 'DELETE FROM warehouses WHERE storeviewid ="' . $storeview_id . '";';
            $queries[] = 'DELETE FROM products_images_lbl WHERE storeviewid ="' . $storeview_id . '";';
            $queries[] = 'DELETE FROM products_categories;';
            $queries[] = 'DELETE FROM categories WHERE storeviewid ="' . $storeview_id . '";';

            $customer_groups = Group::getGroups($id_lang, $id_shop);

            $queries[] = Query::getByName('shopInitializationsAttributes_query', [
                'storeview_id' => $storeview_id,
                'externalidstr_warehouse' => $externalidstr_warehouse,
            ]);

            // creazione attributi variante (prodotti semplici)
            $queries[] = self::generateVariantsQuery($storeview_id, $id_lang);

            // creazione caratteristiche
            $queries[] = self::generateFeaturesQuery($storeview_id, $id_lang);

            // creazione gruppi clienti
            foreach ($customer_groups as $customer_group) {
                $id_group = $customer_group['id_group'];
                $name = $customer_group['name'];
                $externalidstr = $id_shop . '_' . $id_lang . '_' . $id_group;
                $queries[] = Query::getByName('shopInitializationsCustomerGroup_query', [
                    'name' => pSQL($name),
                    'externalidstr' => $externalidstr,
                    'storeview_id' => $storeview_id,
                ]);
            }
        }

        $queries = implode('', $queries);
        /* @phpstan-ignore-next-line */
        if (AccelaSearch::AS_CONFIG['LOG_QUERY']) {
            Db::getInstance()->insert('log', [
                'severity' => 1,
                'error_code' => 0,
                'message' => pSQL($queries),
            ]);
        }

        try {
            Collector::getInstance()->beginTransaction();
            Collector::getInstance()->exec($queries);
            Collector::getInstance()->commit();
        } catch (Exception $e) {
            Collector::getInstance()->rollBack();
            Db::getInstance()->insert('log', [
                'severity' => 1,
                'error_code' => 0,
                'message' => pSQL($e->getMessage()),
            ]);
        }

        self::generateCategories();

        return true;
    }

    /**
     * Crea un array chiave valore degli attributi impostati su AS
     *
     * @param int $storeviewid di AccelaSearch
     *
     * @return array ["label" => id]
     */
    private function getAsAttributesId($storeviewid)
    {
        $attributes = Collector::getInstance()->executeS("SELECT * FROM products_attr_label WHERE storeviewid = $storeviewid");
        $image_attributes = Collector::getInstance()->executeS("SELECT * FROM products_images_lbl WHERE storeviewid = $storeviewid");
        $as_attributes = [];
        foreach ($attributes as $attribute) {
            $as_attributes[$attribute['label']] = $attribute['id'];
        }
        foreach ($image_attributes as $image_attribute) {
            $as_attributes[$image_attribute['label']] = $image_attribute['id'];
        }

        return $as_attributes;
    }

    private function getAsProductTypes()
    {
        $types = Collector::getInstance()->executeS('SELECT * FROM products_types');
        $as_types = [];
        foreach ($types as $type) {
            $as_types[$type['label']] = $type['id'];
        }

        return $as_types;
    }

    private function getUsersGroups($storeviewid)
    {
        $users_groups = Collector::getInstance()->executeS("SELECT * FROM users_groups WHERE storeviewid = $storeviewid");
        $as_groups = [];
        foreach ($users_groups as $user_group) {
            $as_groups[$user_group['externalidstr']] = $user_group['id'];
        }

        return $as_groups;
    }

    public static function generateProductsQueryStatic($id_shop, $id_lang, $as_shop_id, $as_shop_real_id, $limit = '0,1000')
    {
        return (new self())->generateProductsQuery($id_shop, $id_lang, $as_shop_id, $as_shop_real_id, $limit);
    }

    // NOTE: Uncomment the hardcoded return value for testing purposes
    public static function estimateNbProducts($id_shop, $id_lang)
    {
        $query = Query::getByName(
            'estimateNbProducts_query',
            [
                'id_shop' => $id_shop,
                'id_lang' => $id_lang,
            ]
        );
        // return 3;
        return (int) Db::getInstance()->getValue($query);
    }

    public static function hookActionCronjobStatic($wait = true)
    {
        $me = new self();

        return $me->hookActionCronJob($wait);
    }

    /**
     *	Gestore di tutti i cronjob
     *	Azioni principali
     *		- Creazione della coda fullsync locale
     *		- Invio della coda ad AccelaSearch
     *		- Lettura coda differenziale e preparazione query
     *	Stati (Configuration)
     *		- ACCELASEARCH_FULLSYNC_CREATION_PROGRESS
     *			- Impostato a 2 indica che la coda è terminata e si può procedere con l'invio delle query su AS
     *			- Impostato a 1 indica che la coda è in progress e nessuna scrittura deve avviarsi
     *			- Impostato a 0 indica che la coda è pronta per continuare la scrittura
     *			- Se non esiste significa che ancora non è stata avviata la creazione delle query
     *		- ACCELASEARCH_FULLSYNC_PROGRESS
     *			- Impostato a 2 indica che la coda è terminata e si può procedere con la sync differenziale
     *			- Impostato a 1 indica che la coda è in progress e la sync differenziale non deve avviarsi ( ma i trigger possono popolare la coda)
     *			- Impostato a 0 indica che la coda è pronta per scrivere su AccelaSearch
     *			- Se non esiste significa che ancora non è stato avviato nulla
     */
    public function hookActionCronJob($wait = true)
    {
        if ($wait !== true) {
            if (function_exists('set_time_limit')) {
                set_time_limit(0);
            }
            if (function_exists('ignore_user_abort')) {
                ignore_user_abort(true);
            }
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
        }
        $dryrun = AccelaSearch::AS_CONFIG['CRONJOB_DRYRUN'];
        $log_stack = [];
        if (Module::isInstalled($this->name)) {
            // collector check
            try {
                Collector::getInstance();
            } catch (Exception $e) {
                exit('Invalid collector credentials');
            }

            // shop configuration check
            $as_shops = self::getAsShops();
            if (!$as_shops) {
                exit('No shops configured');
            }

            $sync = new AccelaSearch\Sync();

            // la sync non è mai stata avviata
            if ($sync->neverStarted()) {
                exit('Exit without run anything');
            }

            // se ha finito di scrivere le code di creazione
            if ($sync->hasCompletedQueueCreation()) {
                // sync differenziale
                $sync->createQueryByDifferentialQueue();

                // check se c'è un elemento in coda pronto per AS

                Queue::checkAndSendRowToAs();
            }

            // se il flag è in progress
            if ($sync->inProgress()) {
                // check se c'è un elemento in coda pronto per AS
                Queue::checkAndSendRowToAs();

                if ($sync->isLockedByCreationProcess()) {
                    $sync->unlock();
                    $this->hookActionCronJob();
                }

                exit('Fullsync is running');
            }

            // se il flag è libero per la scrittura inizia a scrivere i prodotti per la prima volta
            if ($sync->isAbleToWriteNewRows()) {
                foreach ($as_shops as $as_shop) {
                    $id_shop = $as_shop['id_shop'];
                    $id_lang = $as_shop['id_lang'];
                    $as_shop_id = $as_shop['as_shop_id'];
                    $as_shop_real_id = $as_shop['as_shop_real_id'];

                    $log_stack[] = "Processo ID SHOP: $id_shop e ID LANG: $id_lang";

                    $divider = Queue::getOffsetDivider($id_shop, $id_lang);
                    $queues = Queue::get($id_shop, $id_lang);
                    $products_nb = self::estimateNbProducts($id_shop, $id_lang);

                    $start_cycle = 1;
                    $end_cycle = ceil($products_nb / $divider);

                    $log_stack[] = "Il divider è: $divider ed i prodotti totali da processare: $products_nb - end cycle: $end_cycle";

                    // se c'è almeno un elemento in coda riprendo la generazione da quel punto
                    // fino al suo completamento
                    if (count($queues) > 0) {
                        $queues_end = end($queues);
                        $start_cycle = $queues_end['start_cycle'];
                        $end_cycle = $queues_end['end_cycle'];
                        $id_shop = $queues_end['id_shop'];
                        $id_lang = $queues_end['id_lang'];

                        $log_stack[] = "C'è almeno un elemento in coda in questo contesto, riprendo la generazione da qui. Start cycle: $start_cycle - End cycle: $end_cycle";

                        // se per questo shop e lingua la coda è terminata, passo al prossimo ciclo
                        if ((int) $start_cycle == (int) $end_cycle) {
                            $log_stack[] = 'Skippo questo shop e id_lang perchè la coda è terminata';
                            continue;
                        }
                    }

                    // se il ciclo non è ancora finito
                    if ((int) $start_cycle <= (int) $end_cycle) {
                        // imposto il lock
                        $sync->lock();

                        $log_stack[] = 'Il ciclo non è ancora finito per questo shop e lingua';

                        // incremento di 1 in caso di coda esistente perchè deve scrivere dalla prossima riga a partire dall'ultima trovata
                        $queue_start = (count($queues) === 0) ? $start_cycle : ++$start_cycle;
                        $executions_nb = $end_cycle;

                        for ($start = $queue_start; $start <= $executions_nb; ++$start) {
                            // se non c'erano elementi in coda l'offset va sceso di 1
                            $limit_starter = $start - 1;

                            $limit = $divider * $limit_starter . ',' . $divider;
                            $log_stack[] = "Genero le query e le scrivo nella coda per id shop: $id_shop, id lingua: $id_lang con limit: $limit e start: $start<br><br>";

                            if ($dryrun === false) {
                                $query = AccelaSearch::generateProductsQueryStatic(
                                    $id_shop,
                                    $id_lang,
                                    $as_shop_id,
                                    $as_shop_real_id,
                                    $limit
                                );
                                Queue::create($query, $limit, $start, $executions_nb, $id_shop, $id_lang);
                            }
                        }
                    }
                    AccelaSearch\Sync::reindex($as_shop_real_id);
                }

                // scrittura terminata rilascio del lock e della fullsync creation e lancio reindex remote
                $sync->terminate();
                $this->hookActionCronJob();
            }
        }
        /* @phpstan-ignore-next-line */
        if ($dryrun) {
            echo implode('<br>', $log_stack);
        }
    }

    public static function orderAndFilterRows($rows, $id_shop, $id_lang)
    {
        $products = [];
        foreach ($rows as $row) {
            $id = $row['id'];
            $id_product = $row['id_product'];
            $id_product_attribute = $row['id_product_attribute'];
            $type = $row['type'];
            $row_id_shop = $row['id_shop'];
            $row_id_lang = $row['id_lang'];
            $name = $row['name'];
            $value = $row['value'];
            $operation = $row['op'];

            $row_id_shop = (int) $row_id_shop;
            $row_id_lang = (int) $row_id_lang;
            $id_shop = (int) $id_shop;
            $id_lang = (int) $id_lang;

            // skip if id_shop is not global and not configured on AS
            if ($row_id_shop !== 0 && $row_id_shop !== $id_shop) {
                continue;
            }
            // skip if id_lang is not global and not configured on AS
            if ($row_id_lang !== 0 && $row_id_lang !== $id_lang) {
                continue;
            }

            // NOTE: Gli attribute_image necessitano di aggiornamenti e cascata e non possono essere soggetti dell'accorpamento che avviene successivamente
            if ($type == 'attribute_image') {
                $id_product = $id . '_id_image';
            }

            // add id_product as array key and add new level
            if (!array_key_exists($id_product, $products)) {
                $products[$id_product] = [];
            }

            // add id_product_attribute as array key and add new level
            if (!array_key_exists($id_product_attribute, $products[$id_product])) {
                $products[$id_product][$id_product_attribute] = [];
            }

            // add entity type as array key and add new level
            if (!array_key_exists($type, $products[$id_product][$id_product_attribute])) {
                $products[$id_product][$id_product_attribute][$type] = [];
            }

            // add operation type to each entity as array key and add new level
            if (!array_key_exists($operation, $products[$id_product][$id_product_attribute][$type])) {
                $products[$id_product][$id_product_attribute][$type][$operation] = [];
            }

            // to prevent categories,images assignment override, if a type of update is a category product or attribute_image we reassign a $name and assure it is unique so all updates coming processed
            if ($type == 'category_product' || $type == 'attribute_image' || $type == 'image' || $type == 'feature_product') {
                $name .= '_' . $value;
            }

            // this method assure non useless updates and get access to update prioritization and removal
            $products[$id_product][$id_product_attribute][$type][$operation][$name] = [
                'value' => $value,
                'raw' => $row,
            ];

            // @see sample structure /sample_structure.png
        }

        return $products;
    }

    public static function getDifferentialQueryByRow($id_product, $_row, $id_shop, $id_lang, $as_shop_id, $as_shop_real_id)
    {
        if (!Query::$query_data_manager) {
            throw new \Exception('Cannot perform query without query data manager instance loaded');
        }

        $queries = '';

        $update_context = new AccelaSearch\Updater\UpdateContext($id_shop, $id_lang, $as_shop_id, $as_shop_real_id, $id_product);
        $updater = new AccelaSearch\Updater\Updater($update_context);

        foreach ($_row as $id_product_attribute => $row) {
            $update_context->setIdProductAttribute($id_product_attribute);
            $update_row = new AccelaSearch\Updater\UpdateRow($row);
            $queries .= $updater->getQueries($update_row);
        }

        return $queries;
    }

    public static function cleanProcessedDifferentialRows($start, $end)
    {
        Db::getInstance()->query('DELETE FROM ' . _DB_PREFIX_ . "as_notifications WHERE id >= $start AND id <= $end");
    }

    /**
     *	Dati necessari e comuni a tutte le query
     */
    public static function createQueryDataInstanceByIdShopAndLang($id_shop, $id_lang, $as_shop_id, $as_shop_real_id = 0)
    {
        $link = new Link();
        // un cart ID è obbligatorio per il calcolo dei prezzi
        $currencies = Currency::getCurrenciesByIdShop($id_shop);
        // creo un cart id per ogni valuta, obbligatorio per il calcolo dei prezzi in base a valuta
        $currencies_cart = [];
        foreach ($currencies as $currency) {
            $fake_cart = new Cart();
            $fake_cart->id_currency = isset($currency['id']) ? $currency['id'] : $currency['id_currency'];
            $fake_cart->save();
            $currencies_cart[$currency['iso_code']] = $fake_cart->id;
        }
        $as_instance = (new self());
        $_queryData = [
            'as_shop_id' => $as_shop_id,
            'as_attributes_ids' => $as_instance->getAsAttributesId($as_shop_id),
            'as_product_types' => $as_instance->getAsProductTypes(),
            'as_categories' => self::getAsCategories(),
            'warehouse_id' => Collector::getInstance()->getValue("SELECT id FROM warehouses WHERE storeviewid = $as_shop_id"),
            'customer_groups' => Group::getGroups($id_lang, $id_shop),
            'users_groups' => $as_instance->getUsersGroups($as_shop_id),
            'link' => $link,
            'currencies_cart' => $currencies_cart,
            'as_instance' => $as_instance,
        ];
        $queryData = new QueryData($_queryData);
        // caricando questo oggetto con questi valori, permetto ai metodi statici dentro la classe Query di accedere al context delle chiamate e di non dover richiedere questi dati a ogni query
        Query::loadQueryData($queryData);
    }

    public static function addProductPriceToQueries(
        $id_shop,
        $id_lang,
        $currencies_cart,
        $customer_groups,
        $users_groups,
        $id_product,
        $id_product_attribute,
        &$queries
    ) {
        foreach ($currencies_cart as $iso_code => $cart_id) {
            foreach ($customer_groups as $customer_group) {
                $id_group = $customer_group['id_group'];
                $as_id_group = $users_groups[$id_shop . '_' . $id_lang . '_' . $id_group];

                $id_product_attribute_price = $id_product_attribute === 0 ? null : $id_product_attribute;

                $price = \Product::getPriceStatic(
                    $id_product,
                    true,
                    $id_product_attribute_price,
                    6,
                    null,
                    false,
                    false,
                    1,
                    false,
                    null,
                    $cart_id
                );

                $specialprice = \Product::getPriceStatic(
                    $id_product,
                    true,
                    $id_product_attribute_price,
                    6,
                    null,
                    false,
                    true,
                    1,
                    false,
                    null,
                    $cart_id
                );

                $product_price_externalidstr = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute . '_' . $iso_code;

                $generated_query = Query::getByName('priceUpdate_query', [
                    'as_id_group' => $as_id_group,
                    'price' => $price,
                    'specialprice' => $specialprice,
                    'product_price_externalidstr' => $product_price_externalidstr,
                    'currency' => $iso_code,
                ]);

                $queries[] = $generated_query;
            }
        }
    }

    /**
     * Restituisce i prodotti non ancora presenti su AccelaSearch grazie ad un controllo incrociato tra l'array di prodotti di AS e quello di PS
     *
     * @return array
     */
    public static function getMissingProductsOnAs($id_shop, $id_lang): array
    {
        $_products = Db::getInstance()->executeS('SELECT id_product FROM ' . _DB_PREFIX_ . "product_shop WHERE active = 1 AND id_shop = $id_shop AND id_product != 0");
        $_product_attributes = Db::getInstance()->executeS('SELECT id_product, id_product_attribute FROM ' . _DB_PREFIX_ . "product_attribute_shop WHERE id_shop = $id_shop AND id_product != 0");
        $product_attributes = [];
        foreach ($_product_attributes as $_product_attribute) {
            $id_product = $_product_attribute['id_product'];
            $id_product_attribute = $_product_attribute['id_product_attribute'];

            if (!array_key_exists($id_product, $product_attributes)) {
                $product_attributes[$id_product] = [];
            }
            $product_attributes[$id_product][] = $id_product_attribute;
        }
        $products = [];
        foreach ($_products as $_product) {
            $id_product = $_product['id_product'];
            $ext = $id_shop . '_' . $id_lang . '_' . $id_product . '_0';
            $products[] = $ext;
            if (array_key_exists($id_product, $product_attributes)) {
                foreach ($product_attributes[$id_product] as $product_attribute) {
                    $ext = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $product_attribute;
                    $products[] = $ext;
                }
            }
        }
        $ext_as = $id_shop . '_' . $id_lang . '_';
        $_as_products = Collector::getInstance()->executeS("SELECT externalidstr FROM products WHERE externalidstr LIKE '$ext_as%'");
        $as_products = [];
        foreach ($_as_products as $_as_product) {
            $as_products[] = $_as_product['externalidstr'];
        }
        $diff = array_diff($products, $as_products);
        sort($diff);

        return $diff;
    }

    public static function generateProductsQueryByDifferentialRows(
        $id_shop,
        $id_lang,
        $as_shop_id,
        $as_shop_real_id,
        $limit,
        $cleanAfterComplete = true
    ) {
        $queries = [];

        // Delete all id_product => 0 | It will cause a bug if a product with id 0 exists
        Db::getInstance()->query('DELETE FROM ' . _DB_PREFIX_ . 'as_notifications WHERE id_product = 0');

        // elimino gli aggiornamenti di prezzo singoli se è presente un aggiornamento globale
        $global_price_rule_exist = (bool) Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . "as_notifications WHERE id_product = 0 AND type = 'price' AND id_shop = $id_shop");

        if ($global_price_rule_exist) {
            Db::getInstance()->query('DELETE FROM ' . _DB_PREFIX_ . "as_notifications WHERE id_product != 0 AND type = 'price' AND id_shop = $id_shop");
            Db::getInstance()->query('DELETE FROM ' . _DB_PREFIX_ . "as_notifications WHERE id_product != 0 AND type = 'product' AND id_shop = $id_shop AND name='price'");
        }

        $rows = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . "as_notifications ORDER BY id ASC LIMIT $limit");

        if (empty($rows)) {
            return;
        }

        $rows_id_start = $rows[0]['id'];
        $rows_end = end($rows);
        $rows_id_end = $rows_end['id'];

        $rows = self::orderAndFilterRows($rows, $id_shop, $id_lang);

        self::createQueryDataInstanceByIdShopAndLang($id_shop, $id_lang, $as_shop_id, $as_shop_real_id);

        foreach ($rows as $id_product => $row) {
            $query = self::getDifferentialQueryByRow($id_product, $row, $id_shop, $id_lang, $as_shop_id, $as_shop_real_id);
            if (!empty($query)) {
                $queries[] = $query;
            }
        }

        // dump(implode("", $queries), $rows);die;

        if ($cleanAfterComplete) {
            self::cleanProcessedDifferentialRows($rows_id_start, $rows_id_end);
        }

        return implode('', $queries);
    }

    public static function getRealShopIdByIdShopAndLang($id_shop, $id_lang)
    {
        $as_shops = self::getAsShops();

        return $as_shops[$id_shop . '_' . $id_lang]['as_shop_real_id'];
    }

    // TODO: Add support for other product types bundle, virtual and downloadable
    public static function getProductTypeById($id_product, $id_shop)
    {
        $query = Query::getByName('getProductTypeById_query', [
            'id_product' => $id_product,
            'id_shop' => $id_shop,
        ]);

        return (bool) Db::getInstance()->getValue($query) ? 'Configurable' : 'Simple';
    }

    public static function convertPrestashopTypeToAsType($product_type)
    {
        $type_map = [
            'standard' => 'Simple',
            'combinations' => 'Configurable',
            'virtual' => 'Virtual',
            'pack' => 'Bundle',
        ];
        if (!in_array($product_type, array_keys($type_map))) {
            throw new \Exception('Invalid Product type, passed ' . $product_type . ' and not matched with any type of ' . implode(',', array_keys($type_map)));
        }

        return $type_map[$product_type];
    }

    public static function getProductChildrensById($id_product, $id_shop, $id_lang)
    {
        $query = Query::getByName('getProductChildrensById_query', [
            'id_product' => $id_product,
            'id_shop' => $id_shop,
        ]);

        return Db::getInstance()->executeS($query);
    }

    /**
     * Ottieni le immagini di un prodotto semplice o configurabile
     *
     * Utilizzata nel query generator dei prodotti per acquisire le immagini dei semplici o configurabili, rifattorizzata
     * per avere un metodo comune ad entrambi i tipi prodotto
     *
     * @return array|false associative array of images cover and others, false if not found
     */
    private static function getProductImages(
        int $id_shop,
        int $id_lang,
        int $id_product,
        $id_product_attribute = null,
        $ps_product = null,
        $link_rewrite = ''
    ) {
        $image_response = [
            'cover' => [],
            'others' => [],
        ];

        $others_url = [];
        $link = new Link();

        $images = Image::getImages($id_lang, $id_product, $id_product_attribute);
        if (count($images) === 0) {
            return false;
        }

        $id_product_attribute_sign = ($id_product_attribute === null) ? '0' : $id_product_attribute;
        // search for cover
        $cover_url = null;
        $cover_external_id_str = null;
        foreach ($images as $image) {
            if ((bool) $image['cover']) {
                $cover_url = $link->getImageLink($link_rewrite, $image['id_image']);
                $cover_external_id_str = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute_sign . '_' . $image['id_image'] . '_cover';
            } else {
                $others_url[] = [
                    'url' => $link->getImageLink($link_rewrite, $image['id_image']),
                    'idstr' => $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute_sign . '_' . $image['id_image'] . '_others',
                ];
            }
        }

        // se non c'è nessuna cover imposta la prima immagine dell'array
        if ($cover_url === null) {
            $cover_url = $others_url[0]['url'];
            $cover_external_id_str = str_replace('_others', '_cover', $others_url[0]['idstr']);
        }

        $image_response['cover']['cover_url'] = Tools::getShopProtocol() . $cover_url;
        $image_response['cover']['cover_external_id_str'] = $cover_external_id_str;

        $sort = 2;
        foreach ($others_url as $other_url) {
            $image_response['others'][] = [
                'other_url' => Tools::getShopProtocol() . $other_url['url'],
                'others_url_idstr' => $other_url['idstr'],
                'sort' => $sort,
            ];
            ++$sort;
        }

        return $image_response;
    }

    /**
     * Aggiunge a $queries i children di un configurabile
     */
    public static function addChildrensQuery(
        $childrens,
        $id_shop,
        $id_lang,
        $id_product,
        &$queries,
        $currencies_cart,
        $customer_groups,
        $as_product_types,
        $as_shop_id,
        $warehouse_id,
        $url,
        $ean13_id,
        $isbn_id,
        $upc_id,
        $mpn_id,
        $ps_product,
        $cover_id,
        $others_id,
        $users_groups,
        $sku_id
    ) {
        foreach ($childrens as $children) {
            $id_product_attribute = $children['id_product_attribute'];
            $sku = !empty($children['reference']) ? "'" . $children['reference'] . "'" : 'NULL';
            $typeid = $as_product_types['Simple'];
            $product_external_id_str = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute;
            $qty = $children['real_qty'];

            $ean13 = $children['ean13'];
            $ean13_external = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute . '_ean13';
            $isbn = $children['isbn'];
            $isbn_external = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute . '_isbn';
            $upc = $children['upc'];
            $upc_external = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute . '_upc';
            $mpn = $children['mpn'] ?? '';
            $mpn_external = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute . '_mpn';
            $sku_external = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute . '_sku';

            $images = Image::getImages($id_lang, $id_product, $id_product_attribute);

            $queryData = Query::$query_data_manager;

            $queries[] = Query::getByName('mainProductsChildrenInsert_query', [
                'sku' => $sku,
                'as_shop_id' => $as_shop_id,
                'typeid' => $typeid,
                'product_external_id_str' => $product_external_id_str,
                'url' => $url,
                'warehouse_id' => $warehouse_id,
                'qty' => $qty,
                'ean13' => $ean13,
                'ean13_id' => $ean13_id,
                'ean13_external' => $ean13_external,
                'isbn' => $isbn,
                'isbn_id' => $isbn_id,
                'isbn_external' => $isbn_external,
                'upc' => $upc,
                'upc_id' => $upc_id,
                'upc_external' => $upc_external,
                'mpn' => $mpn,
                'mpn_id' => $mpn_id,
                'mpn_external' => $mpn_external,
                'sku_id' => $sku_id,
                'sku_external' => $sku_external,
            ]);

            // scrivo il valore dell'attributo del semplice
            $attributes = Db::getInstance()->executeS('SELECT id_attribute FROM ' . _DB_PREFIX_ . "product_attribute_combination WHERE id_product_attribute = $id_product_attribute");
            foreach ($attributes as $attribute) {
                // @phpstan-ignore-next-line
                $ps_attribute = version_compare(_PS_VERSION_, '8.0.0', '>') ? new \ProductAttribute($attribute['id_attribute']) : new \Attribute($attribute['id_attribute']);
                // @phpstan-ignore-next-line
                $ps_attribute_group = new AttributeGroup($ps_attribute->id_attribute_group);
                $name = self::sanitize($ps_attribute_group->name[$id_lang]);
                $label_id = $queryData->as_attributes_ids[$name];
                $slug = strtolower(str_replace(' ', '_', self::sanitize($name)));
                $external_id_str = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute . '_' . $slug;
                $queries[] = Query::getByName('addVariant_query', [
                    'label_id' => $label_id,
                    // @phpstan-ignore-next-line
                    'value' => $ps_attribute->name[$id_lang],
                    'is_configurable' => 1,
                    'external_id_str' => $external_id_str,
                ]);
            }

            // immagini specifiche delle varianti
            $images = self::getProductImages($id_shop, $id_lang, $id_product, $id_product_attribute, $children, $ps_product['link_rewrite']);
            if ($images !== false) {
                $cover = $images['cover'];
                $others = $images['others'];

                $queries[] = Query::getByName('mainProductsInsertChildrenImageCover_query', [
                    'product_external_id_str' => $product_external_id_str,
                    'cover_url' => $cover['cover_url'],
                    'cover_id' => $cover_id,
                    'external_id_str' => $cover['cover_external_id_str'],
                ]);

                foreach ($others as $other) {
                    $queries[] = Query::getByName('mainProductsInsertChildrenImagesOthers_query', [
                        'product_external_id_str' => $product_external_id_str,
                        'other_url' => $other['other_url'],
                        'others_url_idstr' => $other['others_url_idstr'],
                        'others_id' => $others_id,
                        'sort' => $other['sort'],
                    ]);
                }
            }

            foreach ($currencies_cart as $iso_code => $cart_id) {
                // prezzi specifici della variante
                foreach ($customer_groups as $customer_group) {
                    $id_group = $customer_group['id_group'];

                    $as_id_group = $users_groups[$id_shop . '_' . $id_lang . '_' . $id_group];

                    $price = Product::getPriceStatic(
                        $id_product,
                        true,
                        $id_product_attribute,
                        6,
                        null,
                        false,
                        false,
                        1,
                        false,
                        null,
                        $cart_id
                    );

                    $specialprice = Product::getPriceStatic(
                        $id_product,
                        true,
                        $id_product_attribute,
                        6,
                        null,
                        false,
                        true,
                        1,
                        false,
                        null,
                        $cart_id
                    );

                    $product_price_externalidstr = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute . '_' . $iso_code;

                    $queries[] = Query::getByName('priceInsertChildren_query', [
                        'as_id_group' => $as_id_group,
                        'price' => $price,
                        'specialprice' => $specialprice,
                        'id_product' => $id_product,
                        'product_price_externalidstr' => $product_price_externalidstr,
                        'currency' => $iso_code,
                    ]);
                }
            }
        }
    }

    public static function getProductCategories($id_product)
    {
        $cats = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . "category_product WHERE id_product = $id_product");
        $cat_ids = [];
        foreach ($cats as $cat) {
            $cat_ids[] = $cat['id_category'];
        }

        return $cat_ids;
    }

    public static function generateProductQueryByProductRow(
        $id_shop,
        $id_lang,
        $as_shop_id,
        $as_shop_real_id,
        $ps_product,
        array &$queries
    ) {
        $queryData = Query::$query_data_manager;
        if (!$queryData) {
            throw new \Exception('Cannot perform product query without query data manager instance');
        }

        $as_product_types = $queryData->as_product_types;

        $name_id = $queryData->as_attributes_ids['name'];
        $short_description_id = $queryData->as_attributes_ids['short_description'];
        $description_id = $queryData->as_attributes_ids['description'];
        $brand_id = $queryData->as_attributes_ids['brand'];
        $ean13_id = $queryData->as_attributes_ids['ean13'];
        $isbn_id = $queryData->as_attributes_ids['isbn'];
        $upc_id = $queryData->as_attributes_ids['upc'];
        $mpn_id = $queryData->as_attributes_ids['mpn'];
        $sku_id = $queryData->as_attributes_ids['sku'];
        $cover_id = $queryData->as_attributes_ids['cover'];
        $others_id = $queryData->as_attributes_ids['others'];

        $as_categories = $queryData->as_categories;
        $warehouse_id = $queryData->warehouse_id;
        $customer_groups = $queryData->customer_groups;
        $users_groups = $queryData->users_groups;
        $link = $queryData->link;
        $currencies_cart = $queryData->currencies_cart;

        $id_product = $ps_product['id_product'];
        $sku = !empty($ps_product['reference']) ? $ps_product['reference'] : md5("SKU_$id_product");
        $product_name = $ps_product['name'];
        $product_short_description = $ps_product['description_short'];
        $product_description = $ps_product['description'];
        $brand = $ps_product['brand'];
        $qty = $ps_product['quantity'];
        $ean13 = $ps_product['ean13'];
        $isbn = $ps_product['isbn'];
        $upc = $ps_product['upc'];

        $url = $link->getProductLink($id_product, null, null, null, $id_lang, $id_shop);
        $product_name = pSQL($product_name);
        $product_short_description = pSQL($product_short_description);
        $product_description = pSQL($product_description);
        $brand = pSQL($brand);
        $mpn = $ps_product['mpn'] ?? '';

        $as_instance = $queryData->as_instance;

        // external idstr
        // crea variabili tipo: $name_external = 1_1_123_name;
        foreach ($as_instance->getAsAttributesId($as_shop_id) as $attr_name => $attr_id) {
            $var_name = $attr_name . '_external';
            $$var_name = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $attr_name;
        }

        $product_type = $ps_product['product_type'] ?? '';
        $product_type = empty($product_type) ? self::getProductTypeById($id_product, $id_shop) : self::convertPrestashopTypeToAsType($product_type);

        $typeid = $as_product_types[$product_type] ?? $as_product_types['Simple'];
        $product_external_id_str = $id_shop . '_' . $id_lang . '_' . $id_product . '_0';

        // product creation e attributes
        $queries[] = Query::getByName('mainProductsInsert_query', [
            'sku' => $sku,
            'as_shop_id' => $as_shop_id,
            'typeid' => $typeid,
            'product_external_id_str' => $product_external_id_str,
            'url' => $url,
            'name_id' => $name_id,
            'product_name' => $product_name,
            /* @phpstan-ignore-next-line */
            'name_external' => $name_external,
            'short_description_id' => $short_description_id,
            'product_short_description' => $product_short_description,
            /* @phpstan-ignore-next-line */
            'short_description_external' => $short_description_external,
            'description_id' => $description_id,
            'product_description' => $product_description,
            /* @phpstan-ignore-next-line */
            'description_external' => $description_external,
            'brand_id' => $brand_id,
            'brand' => $brand,
            /* @phpstan-ignore-next-line */
            'brand_external' => $brand_external,
            'warehouse_id' => $warehouse_id,
            'qty' => $qty,
            'ean13' => $ean13,
            'ean13_id' => $ean13_id,
            /* @phpstan-ignore-next-line */
            'ean13_external' => $ean13_external,
            'isbn' => $isbn,
            'isbn_id' => $isbn_id,
            /* @phpstan-ignore-next-line */
            'isbn_external' => $isbn_external,
            'upc' => $upc,
            'upc_id' => $upc_id,
            /* @phpstan-ignore-next-line */
            'upc_external' => $upc_external,
            'mpn' => $mpn,
            'mpn_id' => $mpn_id,
            /* @phpstan-ignore-next-line */
            'mpn_external' => $mpn_external,
            'sku_id' => $sku_id,
            /* @phpstan-ignore-next-line */
            'sku_external' => $sku_external,
        ]);

        // scrivo le caratteristiche del prodotto nella prima sync
        $features = Db::getInstance()->executeS('SELECT id_feature, id_feature_value FROM ' . _DB_PREFIX_ . "feature_product WHERE id_product = $id_product");
        foreach ($features as $feature) {
            $ps_feature = new Feature($feature['id_feature']);
            $ps_feature_value = new FeatureValue($feature['id_feature_value']);
            $name_feature = self::sanitize($ps_feature->name[$id_lang]);
            $name_feature_value = $ps_feature_value->value[$id_lang];
            $label_id = $queryData->as_attributes_ids[$name_feature];
            $slug = strtolower(str_replace(' ', '_', $name_feature));
            $external_id_str = $id_shop . '_' . $id_lang . '_' . $id_product . '_0_' . $slug;
            $external_id_str_product = $id_shop . '_' . $id_lang . '_' . $id_product . '_0';
            $queries[] = Query::getByName('addFeature_query', [
                'label_id' => $label_id,
                'value' => $name_feature_value,
                'is_configurable' => 0,
                'external_id_str' => $external_id_str,
                'external_id_str_product' => $external_id_str_product,
            ]);
        }

        // assign product categories using AceelaSearch ids
        $product_categories = self::getProductCategories($id_product);
        foreach ($product_categories as $product_category) {
            $as_cat_id = $as_categories[$id_shop . '_' . $id_lang . '_' . $product_category]['id'] ?? false;
            if ($as_cat_id === false) {
                continue;
            }
            $queries[] = Query::getByName('assignProductCategory_query', [
                'as_cat_id' => $as_cat_id,
            ]);
        }

        if ($product_type == 'Configurable') {
            $childrens = self::getProductChildrensById($id_product, $id_shop, $id_lang);
            if (count($childrens) > 0) {
                self::addChildrensQuery(
                    $childrens,
                    $id_shop,
                    $id_lang,
                    $id_product,
                    $queries,
                    $currencies_cart,
                    $customer_groups,
                    $as_product_types,
                    $as_shop_id,
                    $warehouse_id,
                    $url,
                    $ean13_id,
                    $isbn_id,
                    $upc_id,
                    $mpn_id,
                    $ps_product,
                    $cover_id,
                    $others_id,
                    $users_groups,
                    $sku_id
                );
            }
        }

        // Immagini del prodotto semplice / configurabile
        $id_product_attribute = null;
        $id_product_attribute_to_add = '_0';
        $product_external_id_str = $id_shop . '_' . $id_lang . '_' . $id_product . $id_product_attribute_to_add;

        $images = self::getProductImages($id_shop, $id_lang, $id_product, $id_product_attribute, $ps_product, $ps_product['link_rewrite']);
        if ($images !== false) {
            $cover = $images['cover'];
            $others = $images['others'];

            $queries[] = Query::getByName('mainProductsInsertImageCover_query', [
                'product_external_id_str' => $product_external_id_str,
                'cover_url' => $cover['cover_url'],
                'cover_id' => $cover_id,
                'external_id_str' => $cover['cover_external_id_str'],
            ]);

            foreach ($others as $other) {
                $queries[] = Query::getByName('mainProductsInsertImagesOthers_query', [
                    'product_external_id_str' => $product_external_id_str,
                    'other_url' => $other['other_url'],
                    'others_url_idstr' => $other['others_url_idstr'],
                    'others_id' => $others_id,
                    'sort' => $other['sort'],
                ]);
            }
        }

        foreach ($currencies_cart as $iso_code => $cart_id) {
            // prezzi specifici della variante
            foreach ($customer_groups as $customer_group) {
                $id_group = $customer_group['id_group'];
                $as_id_group = $users_groups[$id_shop . '_' . $id_lang . '_' . $id_group];

                $price = Product::getPriceStatic(
                    $id_product,
                    true,
                    null,
                    6,
                    null,
                    false,
                    false,
                    1,
                    false,
                    null,
                    $cart_id
                );

                $specialprice = Product::getPriceStatic(
                    $id_product,
                    true,
                    null,
                    6,
                    null,
                    false,
                    true,
                    1,
                    false,
                    null,
                    $cart_id
                );

                $product_price_externalidstr = $id_shop . '_' . $id_lang . '_' . $id_product . '_0_' . $iso_code;

                $queries[] = Query::getByName('priceInsert_query', [
                    'as_id_group' => $as_id_group,
                    'price' => $price,
                    'specialprice' => $specialprice,
                    'id_product' => $id_product,
                    'product_price_externalidstr' => $product_price_externalidstr,
                    'currency' => $iso_code,
                ]);
            }
        }
    }

    private function generateProductsQuery($id_shop, $id_lang, $as_shop_id, $as_shop_real_id, $limit = '0,1000')
    {
        $prefix = _DB_PREFIX_;

        self::createQueryDataInstanceByIdShopAndLang($id_shop, $id_lang, $as_shop_id, $as_shop_real_id);

        $add_mpn_if_exist = version_compare(_PS_VERSION_, '1.7.7', '<') ? '' : 'p.mpn,';
        $add_product_type_if_exist = version_compare(_PS_VERSION_, '1.7.8', '<') ? '' : 'p.product_type,';

        $ps_products_query = Query::getByName(
            'psProducts_query',
            [
                'id_shop' => $id_shop,
                'id_lang' => $id_lang,
                'limit' => $limit,
                'add_mpn_if_exist' => $add_mpn_if_exist,
                'add_product_type_if_exist' => $add_product_type_if_exist,
                'single_product' => '',
            ]
        );
        $ps_products = Db::getInstance()->executeS($ps_products_query);

        $queries = [];

        foreach ($ps_products as $ps_product) {
            self::generateProductQueryByProductRow($id_shop, $id_lang, $as_shop_id, $as_shop_real_id, $ps_product, $queries);
        }

        return implode('', $queries);
    }

    private function isValidApikey($key)
    {
        $credentials = AccelaSearch::asApi(
            'collector',
            'GET',
            [],
            false,
            [
                'X-Accelasearch-Apikey: ' . $key,
            ]
        );
        $credentials = json_decode($credentials);

        return isset($credentials->status) ? false : true;
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $accelasearch_controller_link = $this->context->link->getAdminLink('AdminAccelaSearchActions');

        // se il cronjob non è mai stato eseguito
        if (!(bool) Configuration::get('ACCELASEARCH_LAST_CRONJOB_EXECUTION') && Configuration::get('ACCELASEARCH_SHOPS_SYNCED') != '{}') {
            $last_view_exec = Configuration::get('ACCELASEARCH_LAST_CRONJOB_PAGEVIEW_EXECUTION');
            $view_exec_times = Configuration::get('ACCELASEARCH_CRONJOB_PAGEVIEW_EXECUTION_TIMES');
            if (!$view_exec_times) {
                Configuration::updateGlobalValue('ACCELASEARCH_CRONJOB_PAGEVIEW_EXECUTION_TIMES', 0);
                $view_exec_times = 0;
            }
            if (!(bool) $last_view_exec) {
                $last_view_exec = time() - 61;
            }
        }

        Media::addJsDef([
            'as_admin_controller' => $accelasearch_controller_link,
            'module_cron_url' => Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . 'modules/accelasearch/cron.php?token=' . Configuration::get('ACCELASEARCH_CRON_TOKEN'),
            '_AS' => [
                'apikey' => Configuration::get('ACCELASEARCH_APIKEY'),
                'translations' => AccelaSearch\Translator::getInstance()->translation_array,
                'base_url' => Tools::getShopDomainSsl(true) . __PS_BASE_URI__,
            ],
        ]);

        $configure = Tools::getValue('configure', null);
        if ($configure == 'accelasearch') {
            $this->context->controller->addCSS(
                'modules/' . $this->name . '/views/css/output.css'
            );

            $this->context->controller->addCSS(
                'modules/' . $this->name . '/views/css/back.css'
            );

            $this->context->controller->addJs(
                'modules/' . $this->name . '/views/js/back.js'
            );
        }
    }

    private function getLangLink($idLang = null, Context $context = null, $idShop = null)
    {
        static $psRewritingSettings = null;
        if ($psRewritingSettings === null) {
            $psRewritingSettings = (int) Configuration::get('PS_REWRITING_SETTINGS', null, null, $idShop);
        }
        if (!$context) {
            $context = Context::getContext();
        }
        if (!Language::isMultiLanguageActivated($idShop) || !$psRewritingSettings) {
            return '';
        }
        if (!$idLang) {
            $idLang = $context->language->id;
        }

        return Language::getIsoById($idLang) . '/';
    }

    public function getCurrentHash()
    {
        $id_shop = $this->context->shop->id;
        $iso = $this->context->language->iso_code;
        $id_lang = $this->context->language->id;
        $link = new Link();

        return md5($link->getBaseLink($id_shop) . $this->getLangLink($id_lang, null, $id_shop) . $iso);
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        $iso = $this->context->currency->iso_code;
        $group = Group::getCurrent();
        $id_lang = $this->context->language->id;
        $group_name = $group->name[$id_lang];
        Media::addJsDef([
            'AS_ADVANCED_CONFIG' => [
                'currencyCode' => $iso,
                'visitorType' => $group_name,
            ],
        ]);
        $this->context->controller->registerJavascript(
            'as-layer',
            'https://svc11.accelasearch.io/API/shops/' . $this->getCurrentHash() . '/loader',
            [
                'priority' => 0,
                'attributes' => 'async',
                'server' => 'remote',
            ]
        );
    }

    public static function getAsShops()
    {
        if (!self::$as_shops_synced) {
            $as_shops = Configuration::get('ACCELASEARCH_SHOPS_SYNCED');
            if (!empty($as_shops) && $as_shops !== '{}') {
                self::$as_shops_synced = json_decode($as_shops, true);
            }
        }

        return self::$as_shops_synced;
    }

    public static function getAsCategories()
    {
        if (!self::$as_categories) {
            $as_categories = Collector::getInstance()->executeS('SELECT * FROM categories');
            $as_categories_indexed = [];
            foreach ($as_categories as $as_category) {
                $as_categories_indexed[$as_category['externalidstr']] = $as_category;
            }
            self::$as_categories = $as_categories_indexed;
        }

        return self::$as_categories;
    }

    public function __construct()
    {
        $this->initializeModule();
    }

    public function hookActionControllerInitBefore()
    {
        // esegue il cronjob se sono passati più di 60s dall'ultima pageview
        if (Configuration::get('ACCELASEARCH_SHOPS_SYNCED') != '{}') {
            $last_view_exec = Configuration::get('ACCELASEARCH_LAST_CRONJOB_PAGEVIEW_EXECUTION');
            $view_exec_times = Configuration::get('ACCELASEARCH_CRONJOB_PAGEVIEW_EXECUTION_TIMES');
            if (!$view_exec_times) {
                Configuration::updateGlobalValue('ACCELASEARCH_CRONJOB_PAGEVIEW_EXECUTION_TIMES', 0);
                $view_exec_times = 0;
            }
            if (!(bool) $last_view_exec) {
                $last_view_exec = time() - 61;
            }

            if ((time() - $last_view_exec) > 60) {
                Configuration::updateGlobalValue('ACCELASEARCH_LAST_CRONJOB_PAGEVIEW_EXECUTION', time());
                Configuration::updateGlobalValue('ACCELASEARCH_CRONJOB_PAGEVIEW_EXECUTION_TIMES', ++$view_exec_times);
                $this->triggerCronjobExternal();
            }
        }
    }

    public function getTriggerQueries()
    {
        $trigger_data = new TriggerDataElements();
        $t_queries = [];
        foreach ($trigger_data->elements as $trigger_def) {
            $triggerDataObject = new TriggerData($trigger_def);
            $trigger = new Trigger($triggerDataObject);
            $t_queries[] = $trigger->getQuery();
        }

        return $t_queries;
    }

    public function getTriggerDeleteQueries()
    {
        $trigger_data = new TriggerDataElements();

        return Trigger::getDeleteQueries($trigger_data->elements);
    }

    public function split_nth($str, $delim, $n)
    {
        return array_map(function ($p) use ($delim) {
            return implode($delim, $p);
        }, array_chunk(explode($delim, $str), $n));
    }

    public function enableMultiStatement()
    {
        file_put_contents(
            _PS_ROOT_DIR_ . '/config/defines.inc.php',
            str_replace(
                "define('_PS_ALLOW_MULTI_STATEMENTS_QUERIES_', false)",
                "define('_PS_ALLOW_MULTI_STATEMENTS_QUERIES_', true)",
                Tools::file_get_contents(_PS_ROOT_DIR_ . '/config/defines.inc.php')
            )
        );
    }

    public function installSql()
    {
        $install_sql = str_replace('{{PREFIX}}', _DB_PREFIX_, Tools::file_get_contents(__DIR__ . '/sql/install.sql'));
        $install_sql = explode(';', $install_sql);
        foreach ($install_sql as $install_sql_query) {
            $install_sql_query = trim($install_sql_query);
            if (empty($install_sql_query)) {
                continue;
            }
            try {
                Db::getInstance()->execute($install_sql_query, false);
            } catch (\Throwable $th) {
                var_dump('FIRST INSTALL QUERY ERROR', $install_sql_query);
            }
        }
        $install_sql = $this->getTriggerDeleteQueries();
        $install_sql = explode(';', $install_sql);
        foreach ($install_sql as $install_sql_query) {
            $install_sql_query = trim($install_sql_query);
            if (empty($install_sql_query)) {
                continue;
            }
            try {
                Db::getInstance()->execute($install_sql_query, false);
            } catch (\Throwable $th) {
                var_dump('TRIGGER DELETE QUERIES ERROR', $install_sql_query);
            }
        }
        $install_sql = $this->getTriggerQueries();
        foreach ($install_sql as $install_sql_query) {
            $install_sql_query = trim($install_sql_query);
            if (empty($install_sql_query)) {
                continue;
            }
            try {
                Db::getInstance()->execute($install_sql_query, false);
            } catch (\Throwable $th) {
                var_dump('TRIGGER QUERIES ERROR', $install_sql_query);
            }
        }
    }

    public function uninstallSql()
    {
        $uninstall_sql = str_replace('{{PREFIX}}', _DB_PREFIX_, Tools::file_get_contents(__DIR__ . '/sql/uninstall.sql'));
        $trigger_data = new TriggerDataElements();
        $uninstall_sql .= Trigger::getDeleteQueries($trigger_data->elements);
        $uninstall_sql = explode(';', $uninstall_sql);
        foreach ($uninstall_sql as $uninstall_sql_query) {
            $uninstall_sql_query = trim($uninstall_sql_query);
            if (empty($uninstall_sql_query)) {
                continue;
            }
            Db::getInstance()->execute($uninstall_sql_query, false);
        }
    }

    public function registerHooks()
    {
        foreach (self::HOOKS as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        return true;
    }

    public function install()
    {
        $this->enableMultiStatement();
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        $this->installSql();

        return
            parent::install()
            && $this->initDefaultConfigurationValues()
            && $this->installTab()
            && $this->registerHooks();
    }

    public function uninstall()
    {
        $this->uninstallSql();

        return
            parent::uninstall()
            && $this->uninstallTab();
    }

    public function getContent()
    {
        Shop::setContext(Shop::CONTEXT_ALL);
        $shops = Shop::getShops(true);
        $languages = Language::getLanguages(true);
        $shops_with_languages = [];

        foreach ($shops as $key => $shop) {
            $available_languages = [];
            foreach ($languages as $language) {
                if ($language['shops'][$shop['id_shop']] === true) {
                    $available_languages[] = $language;
                }
            }
            $shops[$key]['languages'] = $available_languages;
        }

        $this->context->smarty->assign([
            // @phpstan-ignore-next-line
            'module_url' => Context::getContext()->shop->getBaseURL(true) . 'modules/' . $this->name . '/',
            'as_shops' => $shops,
        ]);

        $apikey = Configuration::get('ACCELASEARCH_APIKEY');
        $isValidApikey = false;
        if (!empty($apikey)) {
            $isValidApikey = $this->isValidApikey($apikey);
        }

        $this->context->smarty->assign([
            'AS_apikey' => $apikey,
        ]);

        $tpl = 'configure';
        if ($isValidApikey) {
            $tpl = 'shop_selection';
        }
        $shops_synced = Configuration::get('ACCELASEARCH_SHOPS_SYNCED');
        if ($shops_synced !== '{}') {
            $shops_synced = json_decode($shops_synced, true);
            if (count($shops_synced) > 0) {
                $tpl = 'dashboard';
            }
        }

        $as_shops = self::getAsShops();
        $missing_users_groups = [];
        if (isset($as_shops)) {
            foreach ($as_shops as $as_shop) {
                $id_shop = $as_shop['id_shop'];
                $id_lang = $as_shop['id_lang'];
                $as_shop_id = $as_shop['as_shop_id'];
                $as_shop_real_id = $as_shop['as_shop_real_id'];
                $customer_groups = Group::getGroups($id_lang, $id_shop);
                foreach ($customer_groups as $customer_group) {
                    $id_group = $customer_group['id_group'];
                    $externalidstr = $id_shop . '_' . $id_lang . '_' . $id_group;
                    $group_on_as = (bool) Collector::getInstance()->getValue("SELECT COUNT(*) FROM users_groups WHERE externalidstr = '$externalidstr'");
                    if (!$group_on_as) {
                        $missing_users_groups[] = $externalidstr;
                    }
                }
            }
        }

        $missing_shops = [];
        if (isset($as_shops)) {
            $as_shop_urls = [];
            foreach (Collector::getInstance()->executeS('SELECT url FROM storeviews') as $__shop) {
                $as_shop_urls[] = $__shop['url'];
            }
            $link = new Link();

            foreach ($shops as $shop) {
                $id = $shop['id_shop'];
                foreach ($shop['languages'] as $lang) {
                    $id_lang = $lang['id_lang'];
                    $url = $link->getBaseLink($id) . $this->getLangLink($id_lang, null, $id);
                    if (!in_array($url, $as_shop_urls)) {
                        $missing_shops[] = $url;
                    }
                }
            }
        }

        if (isset($as_shops)) {
            foreach ($as_shops as $as_shop) {
                $id_shop = $as_shop['id_shop'];
                $id_lang = $as_shop['id_lang'];
                $as_shop_id = $as_shop['as_shop_id'];
                $as_shop_real_id = $as_shop['as_shop_real_id'];
                $customer_groups = Group::getGroups($id_lang, $id_shop);
                foreach ($customer_groups as $customer_group) {
                    $id_group = $customer_group['id_group'];
                    $externalidstr = $id_shop . '_' . $id_lang . '_' . $id_group;
                    $group_on_as = (bool) Collector::getInstance()->getValue("SELECT COUNT(*) FROM users_groups WHERE externalidstr = '$externalidstr'");
                    if (!$group_on_as) {
                        $missing_users_groups[] = $externalidstr;
                    }
                }
            }
        }

        $as_shops_synced = [];

        // build shops object from shops synced and get flag icon
        foreach ($as_shops as $as_shop) {
            $id_shop = $as_shop['id_shop'];
            $id_lang = $as_shop['id_lang'];
            $as_shop_id = $as_shop['as_shop_id'];
            $as_shop_real_id = $as_shop['as_shop_real_id'];
            $shop = new Shop($id_shop);
            $language = new Language($id_lang);
            $languageCode = $language->iso_code;
            $flagIcon = Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . 'img/tmp/lang_mini_' . $id_lang . '_' . $id_shop . '.jpg';
            $as_shops_synced[] = [
                'id_shop' => $id_shop,
                'id_lang' => $id_lang,
                'as_shop_id' => $as_shop_id,
                'as_shop_real_id' => $as_shop_real_id,
                'name' => $shop->name,
                'iso_code' => $languageCode,
                'flag' => $flagIcon,
            ];
        }

        $this->context->smarty->assign([
            'tpl_to_render' => $tpl,
            'DEBUG_MODE' => self::AS_CONFIG['DEBUG_MODE'],
            'CRONJOB_EXECUTED' => (bool) Configuration::get('ACCELASEARCH_LAST_CRONJOB_EXECUTION'),
            'CRONJOB_EXECUTED_RECENTLY' => (bool) Configuration::get('ACCELASEARCH_LAST_CRONJOB_EXECUTION') ? ((time() - (int) Configuration::get('ACCELASEARCH_LAST_CRONJOB_EXECUTION')) < 600) : false,
            'PRODUCTS_SYNC_NEVER_STARTED' => ((int) Configuration::get('ACCELASEARCH_FULLSYNC_CREATION_PROGRESS') === 0),
            'PRODUCTS_SYNC_PROGRESS' => ((int) Configuration::get('ACCELASEARCH_FULLSYNC_CREATION_PROGRESS') === 1),
            'PRODUCTS_SYNC_COMPLETED' => ((int) Configuration::get('ACCELASEARCH_FULLSYNC_CREATION_PROGRESS') === 2),
            'MISSING_USERS_GROUPS' => $missing_users_groups,
            'MISSING_SHOPS' => $missing_shops,
            'AS_SHOPS_SYNCED' => $as_shops_synced,
        ]);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/' . $tpl . '.tpl');

        return $output;
    }

    private function initDefaultConfigurationValues()
    {
        foreach (self::DEFAULT_CONFIGURATION as $key => $value) {
            if (!Configuration::get($key)) {
                Configuration::updateGlobalValue($key, $value);
            }
        }
        $token = self::generateToken(40);
        Configuration::updateGlobalValue('ACCELASEARCH_CRON_TOKEN', $token);

        return true;
    }

    private function installTab()
    {
        $languages = Language::getLanguages();
        $tab = new Tab();
        $tab->class_name = 'AdminAccelaSearchActions';
        $tab->module = $this->name;
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminCatalog');
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = 'AccelaSearch';
        }
        try {
            $tab->save();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    private function uninstallTab()
    {
        $tab = (int) Tab::getIdFromClassName('AdminAccelaSearchActions');
        if ($tab) {
            $mainTab = new Tab($tab);
            try {
                $mainTab->delete();
            } catch (Exception $e) {
                echo $e->getMessage();

                return false;
            }
        }

        return true;
    }
}

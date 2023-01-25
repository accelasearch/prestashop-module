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

namespace AccelaSearch;

class Sync
{
    public $fullsync_creation_progress;
    public $fullsync_progress;

    public function __construct()
    {
        $this->fullsync_progress = \Configuration::get('ACCELASEARCH_FULLSYNC_PROGRESS');
        $this->fullsync_creation_progress = \Configuration::get('ACCELASEARCH_FULLSYNC_CREATION_PROGRESS');
    }

    public function neverStarted()
    {
        return $this->fullsync_creation_progress === false;
    }

    public function getDifferentialQueueSize()
    {
        return \Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'as_notifications');
    }

    public function getDifferentialRows($start, $end)
    {
        return \Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . "as_notifications WHERE timex BETWEEN (DATE_SUB('" . $start . "', INTERVAL 2 MINUTE)) AND '" . $end . "'");
    }

    public function createQueryByDifferentialQueue()
    {
        $db = \Db::getInstance();

        $queue_size = $this->getDifferentialQueueSize();
        $divider = Queue::getOffsetDividerByType('DIFFERENTIAL_QUEUE', $queue_size);

        $start_cycle = 1;
        $end_cycle = ceil($queue_size / $divider);

        // shop configuration check
        $as_shops = \AccelaSearch::getAsShops();
        if (!$as_shops) {
            exit('No shops configured');
        }

        foreach ($as_shops as $as_shop) {
            [
                'id_shop' => $id_shop,
                'id_lang' => $id_lang,
                'as_shop_id' => $as_shop_id,
                'as_shop_real_id' => $as_shop_real_id
            ] = $as_shop;

            for ($i = $start_cycle; $i <= $end_cycle; ++$i) {
                $limit_starter = $i - 1;
                $limit = $divider * $limit_starter . ',' . $divider;

                $query = \AccelaSearch::generateProductsQueryByDifferentialRows(
                    $id_shop,
                    $id_lang,
                    $as_shop_id,
                    $as_shop_real_id,
                    $limit
                );

                Queue::create($query, $limit, $i, $end_cycle, $id_shop, $id_lang);
            }
        }
    }

    public function hasCompletedQueueCreation()
    {
        return (int) $this->fullsync_creation_progress === 2;
    }

    public function inProgress()
    {
        return (int) $this->fullsync_creation_progress === 1;
    }

    public function isAbleToWriteNewRows()
    {
        return (int) $this->fullsync_creation_progress === 0;
    }

    public function unlock()
    {
        \Configuration::updateGlobalValue('ACCELASEARCH_FULLSYNC_CREATION_PROGRESS', 0);
    }

    public function lock()
    {
        \Configuration::updateGlobalValue('ACCELASEARCH_FULLSYNC_CREATION_PROGRESS', 1);
    }

    public static function createRepriceRule($id_shop)
    {
        $row = [
            'id_product' => 0,
            'id_product_attribute' => 0,
            'type' => 'price',
            'id_shop' => $id_shop,
            'id_lang' => 0,
            'name' => 'id_product',
            'value' => 0,
            'op' => 'i',
        ];
        \Db::getInstance()->insert('as_notifications', $row);
    }

    public static function startRemoteSync($real_shop_id)
    {
        $start_sync = \AccelaSearch::asApi(
            'shops/' . $real_shop_id . '/synchronization',
            'POST',
            [],
            true
        );
        $start_sync = json_decode($start_sync);
        $status = $start_sync->status ?? null;
        if ($status === 'ERROR') {
            throw new \Exception('An error occured during AccelaSearch start sync');
            \Db::getInstance()->insert('log', [
                'severity' => 3,
                'error_code' => 0,
                'message' => 'An error occured during AccelaSearch start sync: ' . json_encode($start_sync),
                'date_add' => date("Y-m-d H:i:s"),
                'date_upd' => date("Y-m-d H:i:s")
            ]);
        }

        return $start_sync;
    }

    public static function terminateRemoteSync($real_shop_id)
    {
        $end_sync = \AccelaSearch::asApi(
            'shops/' . $real_shop_id . '/synchronization',
            'DELETE',
            [],
            true
        );
        $end_sync = json_decode($end_sync);
        $status = $end_sync->status ?? null;
        if ($status === 'ERROR') {
            throw new \Exception('An error occured during AccelaSearch termination sync');
            \Db::getInstance()->insert('log', [
                'severity' => 3,
                'error_code' => 0,
                'message' => 'An error occured during AccelaSearch termination sync: ' . json_encode($end_sync),
                'date_add' => date("Y-m-d H:i:s"),
                'date_upd' => date("Y-m-d H:i:s")
            ]);
        }

        return $end_sync;
    }

    public static function softDeleteAll()
    {
        $tables = \AccelaSearch::DELETABLE_TABLES;
        $query = '';
        foreach ($tables as $table) {
            $query .= "UPDATE $table SET deleted = 1, lastupdate = NOW();";
        }
        \AS_Collector::getInstance()->query($query);
    }

    public static function deleteAll()
    {
        $tables = \AccelaSearch::DELETABLE_TABLES;
        $query = 'SET FOREIGN_KEY_CHECKS = 0;';
        foreach ($tables as $table) {
            $query .= "DELETE FROM $table;";
        }
        $query .= 'SET FOREIGN_KEY_CHECKS = 1;';
        \AS_Collector::getInstance()->query($query);
    }

    // TODO: Verificare codice del metodo
    public static function isIndexing($real_shop_id)
    {
        $indexation = \AccelaSearch::asApi(
            'shops/' . $real_shop_id . '/status',
            'GET',
            [],
            true
        );
        $indexation = json_decode($indexation);
        $status = $indexation->status ?? null;
        if ($status === 'ERROR') {
            throw new \Exception('An error occured during get sync status on AS');
            \Db::getInstance()->insert('log', [
                'severity' => 3,
                'error_code' => 0,
                'message' => 'An error occured during get sync status on AS: ' . json_encode($indexation),
                'date_add' => date("Y-m-d H:i:s"),
                'date_upd' => date("Y-m-d H:i:s")
            ]);
        }

        return $indexation;
    }

    public static function reindex($real_shop_id)
    {
        $indexation = \AccelaSearch::asApi(
            'shops/' . $real_shop_id . '/index',
            'POST',
            [],
            true
        );
        $indexation = json_decode($indexation);
        $status = $indexation->status ?? null;
        if ($status === 'ERROR') {
            throw new \Exception('An error occured during AccelaSearch termination sync');
            \Db::getInstance()->insert('log', [
                'severity' => 3,
                'error_code' => 0,
                'message' => 'An error occured during AccelaSearch termination sync: ' . json_encode($indexation),
                'date_add' => date("Y-m-d H:i:s"),
                'date_upd' => date("Y-m-d H:i:s")
            ]);
        }

        return $indexation;
    }

    public static function DbCleanup($what = ['products'])
    {
        $queries = [];
        if (in_array('products', $what)) {
            $tables = [
                'products_images',
                'products_attr_datetime',
                'products_attr_float',
                'products_attr_int',
                'products_attr_str',
                'products_attr_text',
                'products_children',
                'stocks',
                'prices',
                'products_categories',
                'products',
            ];
            foreach ($tables as $table) {
                $queries[] = "DELETE FROM $table;";
            }
        }
        \AS_Collector::getInstance()->query(implode('', $queries));
    }

    public function terminate()
    {
        \Configuration::updateGlobalValue('ACCELASEARCH_FULLSYNC_CREATION_PROGRESS', 2);
    }

    public function isLockedByCreationProcess()
    {
        // check se bloccato e relativo sblocco
        $queues = Queue::get();
        if (!(bool) count($queues)) {
            return false;
        }
        [
            'created_at' => $created_at
        ] = end($queues);
        $now = time();
        $last_row_seconds = strtotime($created_at);
        // se sono passati almeno 120 secondi dall'ultima esecuzione
        return (($now - $last_row_seconds) > 120) ? true : false;
    }
}

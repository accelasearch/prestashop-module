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

class Queue
{
    const FLUSH_QUERY_VALUE = true;
    const SEND_QUERY_TO_AS = true;

    public static function getRowsToProcess($id_shop = null, $id_lang = null)
    {
        $where = '';
        if ($id_shop !== null) {
            $where .= " AND id_shop = $id_shop";
        }
        if ($id_lang !== null) {
            $where .= " AND id_lang = $id_lang";
        }

        return \Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . "as_fullsync_queue WHERE 1 $where AND is_processing = 0 ORDER BY id asc");
    }

    public static function checkAndSendRowToAs()
    {
        for ($i = 0; $i < 3; ++$i) {
            $queue = self::getRowsToProcess();
            if ($queue !== false) {
                $id_queue = $queue['id'];
                $update_data = [
                    'is_processing' => 1,
                    'processed_at' => date('Y-m-d H:i:s'),
                ];
                if (self::FLUSH_QUERY_VALUE === true) {
                    $update_data['query'] = '';
                }
                \Db::getInstance()->update(
                    'as_fullsync_queue',
                    $update_data,
                    "id = $id_queue"
                );
                if (empty($queue['query'])) {
                    continue;
                }
                // invio ad AS
                if (self::SEND_QUERY_TO_AS === true) {
                    Sync::startRemoteSync(\AccelaSearch::getRealShopIdByIdShopAndLang($queue['id_shop'], $queue['id_lang']));
                    try {
                        $queries = $queue['query'];
                        Collector::getInstance()->beginTransaction();
                        Collector::getInstance()->exec($queries);
                        Collector::getInstance()->commit();
                    } catch (\Exception $e) {
                        Collector::getInstance()->rollBack();
                        \Db::getInstance()->insert('log', [
                            'severity' => 1,
                            'error_code' => 0,
                            'message' => 'Errore durante exec query: ' . pSQL($e->getMessage()),
                        ]);
                    }
                    Sync::terminateRemoteSync(\AccelaSearch::getRealShopIdByIdShopAndLang($queue['id_shop'], $queue['id_lang']));
                }
            }
        }
    }

    public static function get($id_shop = null, $id_lang = null, $limited = true)
    {
        $where = '';
        if ($id_shop !== null) {
            $where .= " AND id_shop = $id_shop";
        }
        if ($id_lang !== null) {
            $where .= " AND id_lang = $id_lang";
        }
        $limit = $limited ? 'LIMIT 1' : '';

        return \Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . "as_fullsync_queue WHERE 1 $where AND is_processing = 0 ORDER BY id desc $limit");
    }

    public static function getOffsetDividerByType($type = 'PRODUCT', $nb = 0)
    {
        // key = greater than
        // value = divider
        $divider_settings = [
            'PRODUCT' => [
                0 => 100,
                1000 => 200,
                5000 => 500,
                20000 => 1500,
                100000 => 3500,
                300000 => 10000,
                1000000 => 50000,
                10000000 => 500000,
            ],
            'DIFFERENTIAL_QUEUE' => [
                0 => 2500,
                5000 => 5000,
                20000 => 10000,
                50000 => 20000,
                100000 => 100,
                300000 => 100000,
                1000000 => 1000000,
                10000000 => 2000000,
            ],
        ];

        $div_keys = array_keys($divider_settings[$type]);
        foreach ($div_keys as $pos => $gt) {
            if ($nb > $gt && $nb < next($div_keys)) {
                return $divider_settings[$type][$div_keys[$pos]];
            }
        }

        return 1000;
    }

    /**
     * Sulla base della stima dei prodotti ritorna il corretto divisore per avere
     * una sync veloce ed efficiente ( calcola il LIMIT della query che genera la coda )
     */
    public static function getOffsetDivider($id_shop, $id_lang)
    {
        $nb = \AccelaSearch::estimateNbProducts($id_shop, $id_lang);

        return self::getOffsetDividerByType('PRODUCT', $nb);
    }

    public static function create($query, $offset_limit, $start_cycle, $end_cycle, $id_shop, $id_lang)
    {
        // rimuove tab e newlines dalla query per rimpicciolire il payload
        $query = preg_replace("/\r|\n|\t/", ' ', pSQL($query));

        // rimuove caratteri speciali che impattano sull'exec della query
        $query = str_replace('´', '', $query);

        $queue = \Db::getInstance()->insert(
            'as_fullsync_queue',
            [
                'query' => $query,
                'offset_limit' => $offset_limit,
                'start_cycle' => $start_cycle,
                'end_cycle' => $end_cycle,
                'id_shop' => $id_shop,
                'id_lang' => $id_lang,
            ]
        );

        return \Db::getInstance()->Insert_ID();
    }
}

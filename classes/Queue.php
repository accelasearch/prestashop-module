<?php

namespace AccelaSearch;

class Queue
{

  public static function getRowsToProcess($id_shop = null, $id_lang = null)
  {
    $where = "";
    if ($id_shop !== null) {
      $where .= " AND id_shop = $id_shop";
    }
    if ($id_lang !== null) {
      $where .= " AND id_lang = $id_lang";
    }
    return \Db::getInstance()->getRow("SELECT * FROM " . _DB_PREFIX_ . "as_fullsync_queue WHERE 1 $where AND is_processing = 0 ORDER BY id asc");
  }

  public static function checkAndSendRowToAs()
  {
    for ($i = 0; $i < 3; $i++) {
      $queue = self::getRowsToProcess();
      if ($queue !== false) {
        $id_queue = $queue["id"];
        \Db::getInstance()->update(
          "as_fullsync_queue",
          [
            "is_processing" => 1,
            "processed_at" => date("Y-m-d H:i:s"),
            "query" => ""
          ],
          "id = $id_queue"
        );
        if (empty($queue["query"])) continue;
        // invio ad AS
        Sync::startRemoteSync(\AccelaSearch::getRealShopIdByIdShopAndLang($queue["id_shop"], $queue["id_lang"]));
        $as_query_success = \AS_Collector::getInstance()->query($queue["query"]);
        Sync::terminateRemoteSync(\AccelaSearch::getRealShopIdByIdShopAndLang($queue["id_shop"], $queue["id_lang"]));
      }
    }
  }

  public static function get($id_shop = null, $id_lang = null, $limited = true)
  {
    $where = "";
    if ($id_shop !== null) {
      $where .= " AND id_shop = $id_shop";
    }
    if ($id_lang !== null) {
      $where .= " AND id_lang = $id_lang";
    }
    $limit = $limited ? "LIMIT 1" : "";
    return \Db::getInstance()->executeS("SELECT * FROM " . _DB_PREFIX_ . "as_fullsync_queue WHERE 1 $where AND is_processing = 0 ORDER BY id desc $limit");
  }

  public static function getOffsetDividerByType($type = "PRODUCT", $nb)
  {

    // key = greater than
    // value = divider
    $divider_settings = [
      "PRODUCT" => [
        0 => 200,
        1000 => 400,
        5000 => 800,
        20000 => 1500,
        100000 => 3500,
        300000 => 10000,
        1000000 => 50000,
        10000000 => 500000
      ],
      "DIFFERENTIAL_QUEUE" => [
        0 => 2500,
        5000 => 5000,
        20000 => 10000,
        50000 => 20000,
        100000 => 100,
        300000 => 100000,
        1000000 => 1000000,
        10000000 => 2000000
      ],
    ];

    $div_keys = array_keys($divider_settings[$type]);
    foreach ($div_keys as $pos => $gt) {
      if ($nb > $gt && $nb < next($div_keys)) return $divider_settings[$type][$div_keys[$pos]];
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
    return self::getOffsetDividerByType("PRODUCT", $nb);
  }

  public static function create($query, $offset_limit, $start_cycle, $end_cycle, $id_shop, $id_lang)
  {
    // rimuove tab e newlines dalla query per rimpicciolire il payload
    $query = preg_replace("/\r|\n|\t/", " ", pSQL($query));
    $queue = \Db::getInstance()->insert(
      "as_fullsync_queue",
      [
        "query" => $query,
        "offset_limit" => $offset_limit,
        "start_cycle" => $start_cycle,
        "end_cycle" => $end_cycle,
        "id_shop" => $id_shop,
        "id_lang" => $id_lang
      ]
    );
    return \Db::getInstance()->Insert_ID();
  }
}

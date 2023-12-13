<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Api\AsClient;
use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Entity\AsShop;

class UpdateSyncTypeController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        $args = $this->parseArgs();
        $configs = $args['configs'] ?? [];
        if (empty($configs)) {
            $this->error('No configs provided', 400);
        }
        foreach ($configs as $key => $value) {
            if (strpos($key, "_ACCELASEARCH_") !== 0) {
                continue;
            }
            try {
                $shops = json_decode(Config::get("_ACCELASEARCH_SHOPS_TO_SYNC"), true);
                foreach ($shops as $shop) {
                    $as_shop_id = (int) $shop['id_shop_as'] ?? 0;
                    $id_shop = (int) $shop['id_shop'];
                    $id_lang = (int) $shop['id_lang'];
                    if (empty($as_shop_id)) {
                        throw new \Exception("No id_shop_as found for id_shop $id_shop and id_lang $id_lang");
                    }
                    AsClient::deleteSync($as_shop_id);
                    Config::updateValue($key, $value);
                }
            } catch (\Exception $e) {
                $this->error('An error occurred during sync type switching', 400);
            }
        }
    }
}

<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Api\AsClient;
use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Entity\AsShop;
use Accelasearch\Accelasearch\Entity\Shop;
use Context;

class SetShopsController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        $args = $this->parseArgs();
        $shops = $args['shops'] ?? [];
        if (empty($shops)) {
            $this->error('No shops provided', 400);
        }

        try {
            // create shops on AccelaSearch
            foreach ($shops as $shop) {
                $shopObject = new Shop($shop['id_shop'], Context::getContext());
                $url = $shopObject->getUrl($shop["id_lang"]);
                $iso = $shop['iso_code'];
                AsShop::create($url, $iso);
            }

            AsClient::notifyShops();

            Config::updateValue('_ACCELASEARCH_SHOPS_TO_SYNC', json_encode($shops));
            Config::updateValue('_ACCELASEARCH_ONBOARDING', 1);
            $this->success(true);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }

    }
}
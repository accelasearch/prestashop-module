<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Api\AsClient;
use Accelasearch\Accelasearch\Api\DgcalClient;
use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Entity\Shop;

class ApikeyVerifyController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        $args = $this->parseArgs();
        $key = $args['key'] ?? '';
        if (empty($key)) {
            $this->error('Api key is empty', 400);
        }
        $verified = AsClient::apiKeyVerify($key);
        if (!$verified) {
            $this->error('Api key is invalid', 400);
        }
        Config::updateValue('_ACCELASEARCH_API_KEY', $key);
        try {
            $shopInfo = Shop::getMainShopInfo();
            DgcalClient::createInstance(
                $shopInfo["shop_url"],
                $shopInfo["shop_name"],
                json_encode($shopInfo["shop_metadata"])
            );
        } catch (\Exception $e) {
            $this->error('Cannot create shop instance', 500);
        }
        try {
            $credentials = AsClient::getCollectorCredentials();
            Config::updateValue('_ACCELASEARCH_API_COLLECTOR', json_encode($credentials));

        } catch (\Exception $e) {
            $this->error('Api key is invalid', 400);
        }
        $this->success(true);
    }
}
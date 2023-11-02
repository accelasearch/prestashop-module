<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Api\AsClient;
use Accelasearch\Accelasearch\Config\Config;

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
            $credentials = AsClient::getCollectorCredentials();
            Config::updateValue('_ACCELASEARCH_API_COLLECTOR', json_encode($credentials));
        } catch (\Exception $e) {
            $this->error('Api key is invalid', 400);
        }
        $this->success(true);
    }
}
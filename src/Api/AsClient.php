<?php

namespace Accelasearch\Accelasearch\Api;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Exception\AsApiException;
use GuzzleHttp\Client as GuzzleClient;

class AsClient extends GenericClientAbstract
{
    private static $instance = null;
    private $client;

    protected function __construct($defaults = [])
    {
        $this->client = new GuzzleClient($defaults);
    }

    public static function checkRequest($request, $url = "")
    {
        $statusCode = $request->getStatusCode();
        if ($statusCode !== 200)
            throw new AsApiException($url . " returned status code: " . $statusCode);
        $body = $request->getBody()->getContents();
        $body = json_decode($body, true);
        $responseStatus = $body['status'] ?? null;
        if ($responseStatus === "ERROR") {
            throw new AsApiException($url . " returned " . $body['message']);
        }
        return $body;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self(self::getDefaults());
        }
        return self::$instance;
    }

    protected static function getDefaults()
    {
        return [
            'base_url' => Config::ACCELASEARCH_ENDPOINT,
            'timeout' => 5.0,
            'defaults' => [
                "headers" => [
                    "X-Accelasearch-Apikey" => Config::get("_ACCELASEARCH_API_KEY"),
                ]
            ]
        ];
    }

    public static function getCollectorCredentials()
    {
        $request = self::getInstance()->client->get('collector');
        return self::checkRequest($request, 'collector');
    }

    public static function notifyShops()
    {
        $request = self::getInstance()->client->post('shops/notify');
        return self::checkRequest($request, 'shops/notify');
    }

    public static function apiKeyVerify($key): bool
    {
        $request = self::getInstance()->client->get('collector');
        try {
            $req = self::checkRequest($request, 'collector');
            return isset($req['password']);
        } catch (AsApiException $e) {
            return false;
        }
    }

}
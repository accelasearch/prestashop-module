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
        if ($statusCode !== 200) {
            throw new AsApiException($url . " returned status code: " . $statusCode);
        }
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
        ];
    }

    public static function getCollectorCredentials()
    {
        $request = self::getInstance()->client->get(Config::ACCELASEARCH_ENDPOINT . 'collector', ["headers" => ["X-Accelasearch-Apikey" => Config::get("_ACCELASEARCH_API_KEY")]]);
        return self::checkRequest($request, 'collector');
    }

    public static function notifyShops()
    {
        $request = self::getInstance()->client->post(Config::ACCELASEARCH_ENDPOINT . 'shops/notify', ["headers" => ["X-Accelasearch-Apikey" => Config::get("_ACCELASEARCH_API_KEY")]]);
        return self::checkRequest($request, 'shops/notify');
    }

    public static function convertIdCollectorToReal($id_collector)
    {
        $request = self::getInstance()->client->get(Config::ACCELASEARCH_ENDPOINT . "shops/$id_collector/convert", ["headers" => ["X-Accelasearch-Apikey" => Config::get("_ACCELASEARCH_API_KEY")]]);
        $result = self::checkRequest($request, 'shops/' . $id_collector . '/convert');
        return $result["shopIdentifier"];
    }

    public static function apiKeyVerify($key): bool
    {
        $request = self::getInstance()->client->get(Config::ACCELASEARCH_ENDPOINT . 'collector', ["headers" => ["X-Accelasearch-Apikey" => $key]]);
        try {
            $req = self::checkRequest($request, 'collector');
            return isset($req['password']);
        } catch (AsApiException $e) {
            return false;
        }
    }

    public static function updateCmsDataByRealId($id, $data)
    {
        $request = self::getInstance()->client->put(
            Config::ACCELASEARCH_ENDPOINT . "shops/$id/data",
            [
                "headers" => ["X-Accelasearch-Apikey" => Config::get("_ACCELASEARCH_API_KEY")],
                "json" => $data
            ]
        );
        return self::checkRequest($request, "shops/$id/data");
    }

    public static function deleteSync($id)
    {
        $shopRequest = self::getInstance()->client->delete(
            "https://svc11.accelasearch.net/integrations/google-shopping/shops/$id/synchronization",
            [
                "headers" => ["X-Accelasearch-Apikey" => Config::get("_ACCELASEARCH_API_KEY")],
            ]
        );
        $request = self::getInstance()->client->delete(
            Config::ACCELASEARCH_ENDPOINT . "shops/$id/synchronization",
            [
                "headers" => ["X-Accelasearch-Apikey" => Config::get("_ACCELASEARCH_API_KEY")],
            ]
        );
        return self::checkRequest($request, "shops/$id") && self::checkRequest($shopRequest, "integrations/google-shopping/shops/$id");
    }

}

<?php

namespace Accelasearch\Accelasearch\Api;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Exception\AsApiException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Message\Request;

class AsClient
{
    private static $instance = null;
    private $client;

    private function __construct()
    {
        $this->client = new GuzzleClient();
    }

    public function sendRequest($request)
    {
        $req = $this->client->send($request);
        $statusCode = $req->getStatusCode();
        if ($statusCode !== 200)
            throw new AsApiException($request->getUrl() . " returned status code: " . $statusCode);
        $body = $req->getBody()->getContents();
        $body = json_decode($body, true);
        $responseStatus = $body['status'] ?? null;
        if ($responseStatus === "ERROR") {
            throw new AsApiException($request->getUrl() . " returned " . $body['message']);
        }
        return $body;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $uri, $headers = [])
    {
        $request = new Request('GET', $uri, $headers);
        return $this->sendRequest($request);
    }

    public function post(string $uri, $headers = [], $body = null)
    {
        $request = new Request('POST', $uri, $headers, $body);
        return $this->sendRequest($request);
    }

    public function delete(string $uri)
    {
        $request = new Request('DELETE', $uri);
        return $this->sendRequest($request);
    }

    public static function getCollectorCredentials()
    {
        return self::getInstance()->get(Config::ACCELASEARCH_ENDPOINT . 'collector', [
            "X-Accelasearch-Apikey" => Config::get("_ACCELASEARCH_API_KEY"),
        ]);
    }

    public static function notifyShops()
    {
        return self::getInstance()->post(Config::ACCELASEARCH_ENDPOINT . 'shops/notify', [
            "X-Accelasearch-Apikey" => Config::get("_ACCELASEARCH_API_KEY"),
        ]);
    }

    public static function apiKeyVerify($key): bool
    {
        $request = new Request('GET', Config::ACCELASEARCH_ENDPOINT . "collector", [
            "X-Accelasearch-Apikey" => $key
        ]);
        try {
            $req = self::getInstance()->sendRequest($request);
            return isset($req['password']);
        } catch (AsApiException $e) {
            return false;
        }
    }

}
<?php

namespace Accelasearch\Accelasearch\Api;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Exception\AsApiException;
use \AccelaSearch\ProductMapper\Api\Client;
use GuzzleHttp\Message\Request;
use \AccelaSearch\ProductMapper\CollectorFacade;
use \AccelaSearch\ProductMapper\DataMapper\Sql\Shop as ShopMapper;
use \AccelaSearch\ProductMapper\DataMapper\Api\Collector as CollectorMapper;
use GuzzleHttp\Client as GuzzleClient;

class AsClient
{
    private static $instance = null;
    private $client;

    private function __construct()
    {
        $this->client = new GuzzleClient([
            'base_uri' => Config::ACCELASEARCH_ENDPOINT,
            'timeout' => 5.0,
            "headers" => [
                "X-Accelasearch-Apikey" => Config::get("_ACCELASEARCH_API_KEY"),
            ]
        ]);
    }

    public function sendRequest(Request $request)
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

    public function post(string $uri, array $data)
    {
        $request = new Request('POST', $uri, [], json_encode($data));
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

    public static function apiKeyVerify2($key): bool
    {

        $client = Client::fromApiKey($key);
        $collector_mapper = new CollectorMapper($client);
        $collector = $collector_mapper->read();

        $dbh = new \PDO(
            'mysql:host=' . $collector->getHostName() . ';dbname=' . $collector->getDatabaseName(),
            $collector->getUsername(),
            $collector->getPassword(),
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]
        );

        $shop_mapper = ShopMapper::fromConnection($dbh);

        dump($shop_mapper->search());
        die;

        $request = new Request('GET', "collector", [
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
<?php

namespace Accelasearch\Accelasearch\Api;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Exception\DgcalApiException;
use GuzzleHttp\Psr7\Request;
use Prestashop\ModuleLibGuzzleAdapter\ClientFactory;

class DgcalClient
{

    private static $instance = null;
    private $client;
    private function __construct()
    {
        $this->client = (new ClientFactory())->getClient([
            'base_uri' => Config::DGCAL_ENDPOINT,
            'timeout' => 5.0,
            "headers" => [
                "X-Accelasearch-Apikey" => Config::get("_ACCELASEARCH_API_KEY"),
            ]
        ]);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function post(string $uri, array $data)
    {
        $request = new Request(
            'POST',
            $uri,
            [
                "X-Accelasearch-Apikey" => Config::get("_ACCELASEARCH_API_KEY"),
                "Content-Type" => "application/x-www-form-urlencoded",
            ],
            http_build_query($data, null, '&')
        );
        return $this->sendRequest($request);
    }

    public function sendRequest(Request $request)
    {
        $req = $this->client->sendRequest($request);
        $statusCode = $req->getStatusCode();
        if ($statusCode !== 200)
            throw new DgcalApiException($request->getUri() . " returned status code: " . $statusCode);
        $body = $req->getBody()->getContents();
        $body = json_decode($body, true);
        $responseStatus = $body['success'] ?? null;
        if (!$responseStatus) {
            throw new DgcalApiException($request->getUri() . " returned success false");
        }
        return $body;
    }

    public static function createInstance($shop_url, $shop_name, $shop_metadata = null)
    {
        $client = self::getInstance();
        $data = [
            "shop_url" => $shop_url,
            "shop_name" => $shop_name,
            "shop_metadata" => $shop_metadata,
        ];
        $response = $client->post("instances", $data);
        return $response;
    }

    public static function createLog($message, $gravity, $context)
    {
        $client = self::getInstance();
        $data = [
            "message" => $message,
            "gravity" => $gravity,
            "context" => $context,
        ];
        $response = $client->post("logs", $data);
        return $response;
    }
}
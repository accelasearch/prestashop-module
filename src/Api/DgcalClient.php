<?php

namespace Accelasearch\Accelasearch\Api;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Exception\DgcalApiException;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Client as GuzzleClient;

class DgcalClient
{

    private static $instance = null;
    private $client;
    private function __construct()
    {
        $this->client = new GuzzleClient([
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

    public function post(string $uri, $headers = [], $body = null)
    {
        $headers["X-Accelasearch-Apikey"] = Config::get("_ACCELASEARCH_API_KEY");
        $headers["Content-Type"] = "application/x-www-form-urlencoded";
        $post = $this->client->post($uri, [
            "headers" => $headers,
            "body" => http_build_query($body, null, "&")
        ]);
        return $this->checkRequest($post);
    }

    public function checkRequest($req)
    {
        $statusCode = $req->getStatusCode();
        if ($statusCode !== 200)
            throw new DgcalApiException($req->getUrl() . " returned status code: " . $statusCode);
        $body = $req->getBody()->getContents();
        $body = json_decode($body, true);
        $responseStatus = $body['success'] ?? null;
        if (!$responseStatus) {
            throw new DgcalApiException($req->getUrl() . " returned success false");
        }
        return $body;
    }

    public function sendRequest(Request $request)
    {
        $req = $this->client->send($request);
        return $this->checkRequest($req);
    }

    public static function createInstance($shop_url, $shop_name, $shop_metadata = null)
    {
        $client = self::getInstance();
        $data = [
            "shop_url" => $shop_url,
            "shop_name" => $shop_name,
            "shop_metadata" => $shop_metadata,
        ];
        $response = $client->post(Config::DGCAL_ENDPOINT . "instances", [], $data);
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
        $response = $client->post(Config::DGCAL_ENDPOINT . "logs", [], $data);
        return $response;
    }
}
<?php

namespace Accelasearch\Accelasearch\Api;

use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Exception\DgcalApiException;
use GuzzleHttp\Client as GuzzleClient;

class DgcalClient extends GenericClientAbstract
{

    private static $instance = null;
    private $client;
    protected function __construct($defaults = [])
    {
        $this->client = new GuzzleClient($defaults);
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
            'base_url' => Config::DGCAL_ENDPOINT,
            'timeout' => 5.0,
            'defaults' => [
                "headers" => [
                    "X-Accelasearch-Apikey" => Config::get("_ACCELASEARCH_API_KEY"),
                ]
            ]
        ];
    }

    public function get(string $uri, $headers = [])
    {
        $get = self::getInstance()->client->get($uri);
        return $this->checkRequest($get, $uri);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getLatestZip()
    {
        $req = $this->client->get("module/download/latest");
        $statusCode = $req->getStatusCode();
        if ($statusCode !== 200)
            throw new DgcalApiException("module zip download returned status code: " . $statusCode);
        $body = $req->getBody()->getContents();
        return $body;
    }

    public function getLatestVersion()
    {
        try {
            $req = $this->client->get("module/getLatestVersion");
            $req = $this->checkRequest($req);
            return $req['data']['version'] ?? null;
        } catch (\Exception $e) {
            return null;
        }

    }

    public function post(string $uri, $headers = [], $body = null)
    {
        $headers["Content-Type"] = "application/x-www-form-urlencoded";
        $post = self::getInstance()->client->post($uri, [
            "headers" => $headers,
            "body" => http_build_query($body, "", "&")
        ]);
        return $this->checkRequest($post);
    }

    public function checkRequest($req, $url = "")
    {
        $statusCode = $req->getStatusCode();
        if ($statusCode !== 200)
            throw new DgcalApiException($url . " returned status code: " . $statusCode);
        $body = $req->getBody()->getContents();
        $body = json_decode($body, true);
        $responseStatus = $body['success'] ?? null;
        if ($responseStatus === false) {
            throw new DgcalApiException($url . " returned success false");
        }
        return $body;
    }

    public static function createInstance($shop_url, $shop_name, $shop_metadata = null)
    {
        $client = self::getInstance()->client;
        $data = [
            "shop_url" => $shop_url,
            "shop_name" => $shop_name,
            "shop_metadata" => $shop_metadata,
        ];
        $response = $client->post("instances", [
            "body" => $data
        ]);
        self::getInstance()->checkRequest($response);
        return $response;
    }

    public static function createLog($message, $gravity, $context)
    {
        $client = self::getInstance()->client;
        $data = [
            "message" => $message,
            "gravity" => $gravity,
            "context" => $context,
        ];
        $response = $client->post("logs", [
            "body" => $data
        ]);
        self::getInstance()->checkRequest($response);
        return $response;
    }
}
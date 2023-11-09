<?php

namespace Accelasearch\Accelasearch\Controller;

abstract class AbstractController
{

    private $module;
    public function __construct(\Module $module)
    {
        $this->module = $module;
    }
    public function response($data)
    {
        header('Content-Type: application/json');
        die(json_encode($data));
    }

    public function success($data = [])
    {
        $this->response(['success' => true, 'data' => $data]);
    }

    public function error($data = [], $statusCode = 200)
    {
        http_response_code($statusCode);
        $this->response(['success' => false, 'data' => $data]);
    }

    /**
     * Parse arguments from JSON input
     */
    public function parseArgs()
    {
        $input = file_get_contents('php://input');
        $args = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON input');
        }
        return $args;
    }
}
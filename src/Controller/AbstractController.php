<?php

namespace Accelasearch\Accelasearch\Controller;

abstract class AbstractController
{
    public function response($data)
    {
        header('Content-Type: application/json');
        die(json_encode($data));
    }

    public function success($data = [])
    {
        $this->response(['success' => true, 'data' => $data]);
    }

    public function error($data = [])
    {
        $this->response(['success' => false, 'data' => $data]);
    }
}
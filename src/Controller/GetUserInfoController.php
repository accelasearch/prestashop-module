<?php

namespace Accelasearch\Accelasearch\Controller;

class GetUserInfoController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        $this->success([
            "logged" => false,
            "onBoarding" => 0
        ]);
    }
}
<?php

namespace Accelasearch\Accelasearch\Controller;

class GetShopsController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        $shops = \Shop::getShops();
        $this->success($shops);
    }
}
<?php

namespace Accelasearch\Accelasearch\Controller;

use Accelasearch\Accelasearch\Entity\Shop;

class GetShopsController extends AbstractController implements ControllerInterface
{
    public function handleRequest()
    {
        $shopsInfo = Shop::getShopsInfo();
        $shops = [];
        foreach ($shopsInfo as $shop) {
            foreach ($shop["languages"] as $language) {
                $id_shop = $shop["id_shop"];
                $id_lang = $language["id_lang"];
                $shops[] = [
                    "id_shop" => $id_shop,
                    "id_lang" => $id_lang,
                    "name" => $shop["name"] . " - " . $language["name"],
                    "iso_code" => $language["iso_code"],
                    "flagIcon" => $language["flagIcon"]
                ];
            }
        }
        $this->success($shops);
    }
}
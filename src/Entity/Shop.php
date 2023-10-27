<?php

namespace Accelasearch\Accelasearch\Entity;

use Accelasearch\Accelasearch\Exception\ShopNotFoundException;

class Shop
{
    private $id;
    public $ps;
    public function __construct(int $id)
    {
        if (\Shop::getShop($id) === false)
            throw new ShopNotFoundException($id);
        $this->id = $id;
        $this->ps = new \Shop($id);
    }
    public function getId(): int
    {
        return $this->id;
    }
}
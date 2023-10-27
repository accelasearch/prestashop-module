<?php

namespace Accelasearch\Accelasearch\Repository;

class ProductRepository
{
    public function getProducts(int $id_lang, int $start, int $limit): array
    {
        return \Product::getProducts($id_lang, $start, $limit, 'id_product', 'ASC');
    }
}
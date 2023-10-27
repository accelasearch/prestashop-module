<?php

namespace Accelasearch\Accelasearch\Service;

interface ServiceInterface
{
    public function getProducts(int $id_lang, int $start, int $limit): array;
}
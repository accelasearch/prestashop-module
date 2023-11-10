<?php

namespace Accelasearch\Accelasearch\Service;

use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Entity\Shop;

interface ServiceInterface
{
    public function getProducts(Shop $shop, Language $language, int $start, int $limit, $progressIndicator = null): array;
}
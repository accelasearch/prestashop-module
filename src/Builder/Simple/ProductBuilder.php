<?php

namespace Accelasearch\Accelasearch\Builder\Simple;

use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Entity\Shop;
use Accelasearch\Accelasearch\Builder\ProductBuilderAbstract;

class ProductBuilder extends ProductBuilderAbstract
{
    public function build(Shop $shop, Language $language)
    {
        parent::build($shop, $language);
    }

}
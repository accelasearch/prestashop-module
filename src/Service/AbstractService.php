<?php

namespace Accelasearch\Accelasearch\Service;

abstract class AbstractService
{
    protected $productRepository;
    protected $productDecorator;
    protected $config;
    public function __construct(
        \Accelasearch\Accelasearch\Repository\ProductRepository $productRepository,
        \Accelasearch\Accelasearch\Decorator\ProductDecorator $productDecorator,
        \Accelasearch\Accelasearch\Config\Config $config
    ) {
        $this->productRepository = $productRepository;
        $this->productDecorator = $productDecorator;
        $this->config = $config;
    }

    public function getProductsNb($id_shop, $id_lang): int
    {
        return $this->productRepository->getProductsNb($id_shop, $id_lang);
    }
}
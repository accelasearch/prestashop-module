<?php

namespace Accelasearch\Accelasearch\Factory;

use Accelasearch\Accelasearch\Repository\ProductRepository;
use Accelasearch\Accelasearch\Decorator\ProductDecorator;
use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Service\AllConfigurableService;
use Accelasearch\Accelasearch\Service\AllSimpleService;
use Accelasearch\Accelasearch\Service\AllConfigurableWithSimpleService;

class ProductDataFactory
{
    private static $configurations = [
        'all_configurable' => AllConfigurableService::class,
        'all_simple' => AllSimpleService::class,
        'all_configurable_with_simple' => AllConfigurableWithSimpleService::class,
    ];

    public static function create(
        ProductRepository $productRepository,
        ProductDecorator $productDecorator,
        Config $config,
        $method
    ) {
        if (isset(self::$configurations[$method])) {
            $className = self::$configurations[$method];
            return new $className($productRepository, $productDecorator, $config);
        } else {
            throw new \Exception('Invalid product data service');
        }
    }
}

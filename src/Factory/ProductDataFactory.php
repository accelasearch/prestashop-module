<?php

namespace Accelasearch\Accelasearch\Factory;

use Accelasearch\Accelasearch\Repository\ProductRepository;
use Accelasearch\Accelasearch\Decorator\ProductDecorator;
use Accelasearch\Accelasearch\Config\Config;
use Accelasearch\Accelasearch\Service\AllConfigurableService;
use Accelasearch\Accelasearch\Service\AllSimpleService;
use Accelasearch\Accelasearch\Service\AllConfigurableWithSimpleService;

/**
 * 
 * This class is responsible for creating instances of different product data services based on the given method.
 * It contains a static array of available configurations and a create method that takes in a ProductRepository, 
 * ProductDecorator, Config and a method parameter. The method parameter is used to determine which product data 
 * service to create. If the method parameter matches an available configuration, an instance of the corresponding 
 * service is created and returned. If the method parameter does not match any available configuration, an exception 
 * is thrown.
 */
class ProductDataFactory
{
    public static $configurations = [
        'CONFIGURABLE' => AllConfigurableService::class,
        'SIMPLE' => AllSimpleService::class,
        'CONFIGURABLE_WITH_SIMPLE' => AllConfigurableWithSimpleService::class,
    ];

    /**
     * Creates a product data service based on the given method.
     *
     * @param ProductRepository $productRepository The product repository.
     * @param ProductDecorator $productDecorator The product decorator.
     * @param Config $config The configuration.
     * @param string $method The method to use for creating the product data service.
     *
     * @return object The created product data service.
     *
     * @throws \Exception If an invalid product data service is requested.
     */
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

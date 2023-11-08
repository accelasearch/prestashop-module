<?php

namespace Accelasearch\Accelasearch\Factory;

use Vitalybaev\GoogleMerchant\Product as GoogleShoppingProduct;
use Accelasearch\Accelasearch\Builder\ProductBuilderAbstract;
use Accelasearch\Accelasearch\Builder\Configurable\ProductBuilder as ConfigurableProductBuilder;
use Accelasearch\Accelasearch\Builder\Simple\ProductBuilder as SimpleProductBuilder;
use Accelasearch\Accelasearch\Builder\ConfigurableWithSimple\ProductBuilder as ConfigurableWithSimpleProductBuilder;

/**
 * This class is responsible for creating product builders based on the configuration provided.
 * It contains a static array of available configurations and a static method to create the product builder.
 */
class ProductBuilderFactory
{
    public static $configurations = [
        'CONFIGURABLE' => ConfigurableProductBuilder::class,
        'SIMPLE' => SimpleProductBuilder::class,
        'CONFIGURABLE_WITH_SIMPLE' => ConfigurableWithSimpleProductBuilder::class,
    ];

    /**
     * Create a product builder instance based on the given configuration.
     *
     * @param mixed $product The product to build.
     * @param GoogleShoppingProduct $item The Google Shopping product to use as a reference.
     * @param string $config The configuration to use for building the product.
     *
     * @return ProductBuilderAbstract The product builder instance.
     *
     * @throws \Exception If the configuration is invalid or the class does not exist.
     */
    public static function create(
        $product,
        GoogleShoppingProduct $item,
        $config
    ): ProductBuilderAbstract {
        $class = self::$configurations[$config];
        if (
            !class_exists($class)
            || !is_subclass_of($class, ProductBuilderAbstract::class)
        ) {
            throw new \Exception("Invalid configuration");
        }
        return new $class($product, $item);
    }
}

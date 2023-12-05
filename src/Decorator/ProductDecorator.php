<?php

namespace Accelasearch\Accelasearch\Decorator;

use Accelasearch\Accelasearch\Entity\Language;
use Accelasearch\Accelasearch\Entity\Shop;
use Accelasearch\Accelasearch\Formatter\ArrayFormatter;
use Accelasearch\Accelasearch\Repository\CategoryRepository;
use Accelasearch\Accelasearch\Repository\ProductRepository;

class ProductDecorator
{
    private $productRepository;
    private $shop;
    private $language;
    private $context;
    private $categoryRepository;
    /**
     * @var ArrayFormatter
     */
    private $arrayFormatter;
    public function __construct(
        ProductRepository $productRepository,
        Shop $shop,
        Language $language,
        ArrayFormatter $arrayFormatter,
        \Context $context,
        CategoryRepository $categoryRepository
    ) {
        $this->productRepository = $productRepository;
        $this->shop = $shop;
        $this->language = $language;
        $this->arrayFormatter = $arrayFormatter;
        $this->context = $context;
        $this->categoryRepository = $categoryRepository;
    }

    private function addFeatureValues(array &$products, $langId)
    {
        $productIds = $this->arrayFormatter->formatValueArray($products, 'id_product', true);
        $features = $this->productRepository->getProductFeatures($productIds, $langId);

        foreach ($products as &$product) {
            $product['features'] = isset($features[$product['id_product']]) ? $features[$product['id_product']] : '';
        }
    }

    /**
     * @param array $products
     * @param int $langId
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     */
    private function addAttributeValues(array &$products, $langId)
    {
        $attributeIds = $this->arrayFormatter->formatValueArray($products, 'id_attribute', true);
        $attributes = $this->productRepository->getProductAttributeValues($attributeIds, $langId);

        foreach ($products as &$product) {
            $product['attributes'] = isset($attributes[$product['id_attribute']]) ? $attributes[$product['id_attribute']] : '';
        }
    }

    /**
     * @param array $products
     *
     * @return void
     *
     * @throws \PrestaShopDatabaseException
     */
    private function addImages(array &$products)
    {
        $productIds = $this->arrayFormatter->formatValueArray($products, 'id_product', true);
        $attributeIds = $this->arrayFormatter->formatValueArray($products, 'id_attribute', true);
        $images = $this->productRepository->getProductImages($productIds);

        $attributeImages = $this->productRepository->getAttributeImages($attributeIds);

        foreach ($products as &$product) {
            $coverImageId = '0';

            $productImages = array_filter($images, function ($image) use ($product) {
                return $image['id_product'] === $product['id_product'];
            });

            foreach ($productImages as $productImage) {
                if ($productImage['cover'] == 1) {
                    $coverImageId = $productImage['id_image'];
                    break;
                }
            }

            // Product is without attributes -> get product images
            if ($product['id_attribute'] == 0) {
                $productImageIds = $this->arrayFormatter->formatValueArray($productImages, 'id_image');
            } else {
                $productAttributeImages = array_filter($attributeImages, function ($image) use ($product) {
                    return $image['id_product_attribute'] === $product['id_attribute'];
                });

                // If combination has some pictures -> the first one is the cover
                if (count($productAttributeImages)) {
                    $productImageIds = $this->arrayFormatter->formatValueArray($productAttributeImages, 'id_image');
                    $coverImageId = reset($productImageIds);
                }
                // Fallback on cover & images of the product when no pictures are chosen
                else {
                    $productImageIds = $this->arrayFormatter->formatValueArray($productImages, 'id_image');
                }
            }

            $productImageIds = array_diff($productImageIds, [$coverImageId]);

            $product['images'] = $this->arrayFormatter->arrayToString(
                array_map(function ($imageId) use ($product) {
                    return $this->context->link->getImageLink($product['link_rewrite'], (string) $imageId);
                }, $productImageIds)
            );

            $product['cover'] = $coverImageId == '0' ?
                '' :
                $this->context->link->getImageLink($product['link_rewrite'], (string) $coverImageId);
        }
    }

    public function decorateProducts(array $products): array
    {

        $this->addFeatureValues($products, $this->language->getId());
        $this->addAttributeValues($products, $this->language->getId());
        $this->addImages($products);

        foreach ($products as &$product) {
            $this->addLink($product);
            $this->addAttributeId($product);
            $this->addProductPrices($product);
            $this->addCategoryTree($product);
        }
        return $products;
    }

    /**
     * @param array $product
     *
     * @return void
     */
    private function addCategoryTree(array &$product)
    {
        /** @var int $shopId */
        $shopId = $this->context->shop->id;
        $categoryPaths = $this->categoryRepository->getCategoryPaths(
            $product['id_category_default'],
            $this->language->getId(),
            $shopId
        );

        $product['category_path'] = $categoryPaths['category_path'];
        $product['category_id_path'] = $categoryPaths['category_id_path'];
    }

    /**
     * @param array $product
     *
     * @return void
     */
    private function addAttributeId(array &$product)
    {
        $product['id_product_attribute'] = "{$product['id_product']}-{$product['id_attribute']}";
    }

    /**
     * @param array $product
     *
     * @return void
     */
    private function addProductPrices(array &$product)
    {
        $product['price_tax_incl'] =
            (float) $this->productRepository->getPriceTaxIncluded($product['id_product'], $product['id_attribute']);
        $product['sale_price_tax_incl'] =
            (float) $this->productRepository->getSalePriceTaxIncluded($product['id_product'], $product['id_attribute']);
    }

    private function addLink(array &$product)
    {
        try {
            $product['link'] = $this->context->link->getProductLink(
                $product['id_product'],
                null,
                null,
                null,
                $this->language->getId(),
                $this->shop->getId()
            );
        } catch (\PrestaShopException $e) {
            $product['link'] = '';
        }
    }
}
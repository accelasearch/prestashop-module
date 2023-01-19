<?php

interface Operation
{
    public function generateQueries(UpdateRow $update_row, UpdateContext $context): self;

    public function getQueries(): string;
}

interface RowOperations
{
    public function isDeleteOperation(): bool;

    public function isInsertOperation(): bool;

    public function isUpdateOperation(): bool;
}

class Updater
{
    private $context;
    private $update_stack = [];

    public function __construct(UpdateContext $context)
    {
        $this->context = $context;
    }

    public function populateUpdateStack(UpdateRow $update_row)
    {
        $types = $update_row->getTypes();
        $to_remove = $update_row->getOperationsToRemoveFromStack();
        $types = array_diff($types, $to_remove);
        if (in_array('product', $types)) {
            $this->addToStack(new ProductUpdate());
        }
        if (in_array('image', $types)) {
            $this->addToStack(new ImageUpdate());
        }
        if (in_array('stock', $types)) {
            $this->addToStack(new StockUpdate());
        }
        if (in_array('price', $types)) {
            $this->addToStack(new PriceUpdate());
        }
        if (in_array('category', $types)) {
            $this->addToStack(new CategoryUpdate());
        }
        if (in_array('category_product', $types)) {
            $this->addToStack(new CategoryProductUpdate());
        }
        if (in_array('attribute_image', $types)) {
            $this->addToStack(new AttributeImageUpdate());
        }
        if (in_array('variant', $types)) {
            $this->addToStack(new VariantUpdate());
        }
    }

    public function getQueries(UpdateRow $update_row): string
    {
        $this->populateUpdateStack($update_row);
        $queries = '';
        foreach ($this->update_stack as $op => $update) {
            $update_row->setEntity($op);
            $queries .= $update->generateQueries($update_row, $this->context)->getQueries();
        }
        if (!$this->context->isGlobalOperation() && !empty($queries)) {
            $externalidstr = $this->context->buildExternalId([$this->context->id_product, '']);
            $timestamp = date('Y-m-d H:i:s');
            $queries .= "UPDATE products SET lastupdate = '$timestamp' WHERE externalidstr LIKE '$externalidstr%';";
        }

        return $queries;
    }

    public function setContext(UpdateContext $context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function addToStack(UpdateOperation $operation)
    {
        $this->update_stack[$operation->getName()] = $operation;
    }

    public function removeFromStack(UpdateOperation $operation)
    {
        unset($this->update_stack[$operation->getName()]);
    }
}

class UpdateContext
{
    public $id_shop;
    public $id_lang;
    public $as_shop_id;
    public $as_shop_real_id;
    public $id_product;
    public $id_product_attribute;

    public function __construct($id_shop, $id_lang, $as_shop_id, $as_shop_real_id, $id_product = 0, $id_product_attribute = 0)
    {
        $this->id_shop = $id_shop;
        $this->id_lang = $id_lang;
        $this->as_shop_id = $as_shop_id;
        $this->as_shop_real_id = $as_shop_real_id;
        $this->id_product = $id_product;
        $this->id_product_attribute = $id_product_attribute;
    }

    public function setIdProductAttribute(int $id_product_attribute)
    {
        $this->id_product_attribute = $id_product_attribute;
    }

    public function isGlobalOperation(): bool
    {
        return (int) $this->id_product === 0;
    }

    public function buildExternalId(array $append): string
    {
        $append = implode('_', $append);

        return $this->id_shop . '_' . $this->id_lang . '_' . $append;
    }
}

abstract class UpdateOperation
{
    private $name;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}

class UpdateRow implements RowOperations
{
    private $row;
    private $entity;
    private $to_remove = [];

    public function __construct(array $row)
    {
        $this->row = $row;
    }

    public function getTypes(): array
    {
        return array_keys($this->row);
    }

    public function setEntity(string $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function getRow()
    {
        return $this->row[$this->getEntity()];
    }

    public function setRow(array $row)
    {
        $this->row = $row;
    }

    public function isDeleteOperation(): bool
    {
        return isset($this->row[$this->getEntity()]['d']);
    }

    public function isInsertOperation(): bool
    {
        return isset($this->row[$this->getEntity()]['i']);
    }

    public function isUpdateOperation(): bool
    {
        return isset($this->row[$this->getEntity()]['u']);
    }

    public function unsetOperationIfExist(string $op)
    {
        if (isset($this->row[$op])) {
            unset($this->row[$op]);
        }
    }

    public function removeFromStack(string $op)
    {
        $this->to_remove[] = $op;
    }

    public function getOperationsToRemoveFromStack(): array
    {
        return $this->to_remove;
    }
}

class ProductUpdate extends UpdateOperation implements Operation
{
    private $queries = '';

    public function __construct()
    {
        $this->setName('product');
    }

    public function generateQueries(UpdateRow $update_row, UpdateContext $context): self
    {
        $id_product = $context->id_product;
        $id_product_attribute = $context->id_product_attribute;

        if ($update_row->isDeleteOperation()) {
            $this->queries .= AccelaSearch\Query::getByName('remote_product_delete', [
              'product_external_id_str' => $context->buildExternalId([$id_product, 0]),
            ]);
        }

        if ($update_row->isInsertOperation()) {
            $update_row->unsetOperationIfExist('u');
            $update_row->removeFromStack('image');
            $update_row->removeFromStack('stock');
            $update_row->removeFromStack('price');
            $update_row->removeFromStack('category_association');
            $update_row->removeFromStack('attribute_image');
            $update_row->removeFromStack('variant');
            $this->queries .= AccelaSearch\Query::getProductCreationQuery($id_product, $context->id_shop, $context->id_lang, $context->as_shop_id, $context->as_shop_real_id);
        }

        if ($update_row->isUpdateOperation()) {
            foreach ($update_row->getRow()['u'] as $entity => $update) {
                $this->queries .= AccelaSearch\Query::getProductUpdateQueryByEntity($update['raw'], $context->id_shop, $context->id_lang);
            }
        }

        return $this;
    }

    public function getQueries(): string
    {
        return $this->queries;
    }
}

class ImageUpdate extends UpdateOperation implements Operation
{
    private $queries = '';

    public function __construct()
    {
        $this->setName('image');
    }

    public function generateQueries(UpdateRow $update_row, UpdateContext $context): self
    {
        $id_product = $context->id_product;
        $id_product_attribute = $context->id_product_attribute;

        if ($update_row->isDeleteOperation()) {
            foreach ($update_row->getRow()['d'] as $id_image_str => $im_update) {
                [
                    'id_product' => $row_id_product,
                    'id_product_attribute' => $row_id_product_attribute,
                    'value' => $id_image
                ] = $im_update['raw'];
                $image_external_id_cover = $context->buildExternalId([
                  $row_id_product,
                  $row_id_product_attribute,
                  $id_image,
                  'cover',
                ]);
                $image_external_id_others = $context->buildExternalId([
                  $row_id_product,
                  $row_id_product_attribute,
                  $id_image,
                  'others',
                ]);
                $this->queries .= "UPDATE products_images SET deleted = 1 WHERE externalidstr = '$image_external_id_cover';";
                $this->queries .= "UPDATE products_images SET deleted = 1 WHERE externalidstr = '$image_external_id_others';";
            }
        }

        if ($update_row->isInsertOperation()) {
            foreach ($update_row->getRow()['i'] as $id_image_str => $im_update) {
                [
                    'id_product' => $row_id_product,
                    'id_product_attribute' => $row_id_product_attribute,
                    'value' => $id_image
                ] = $im_update['raw'];

                $this->queries .= AccelaSearch\Query::getProductImageByIdQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang, $id_image);
            }
        }

        if ($update_row->isUpdateOperation()) {
            foreach ($update_row->getRow()['u'] as $id_image_str => $im_update) {
                [
                    'id_product' => $row_id_product,
                    'id_product_attribute' => $row_id_product_attribute,
                    'value' => $id_image
                ] = $im_update['raw'];
                $this->queries .= AccelaSearch\Query::getProductImageByIdQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang, $id_image);
            }
        }

        return $this;
    }

    public function getQueries(): string
    {
        return $this->queries;
    }
}

class StockUpdate extends UpdateOperation implements Operation
{
    private $queries = '';

    public function __construct()
    {
        $this->setName('stock');
    }

    public function generateQueries(UpdateRow $update_row, UpdateContext $context): self
    {
        $id_product = $context->id_product;
        $id_product_attribute = $context->id_product_attribute;

        if ($update_row->isUpdateOperation()) {
            [
                'id_product' => $row_id_product,
                'id_product_attribute' => $row_id_product_attribute,
                'value' => $quantity
            ] = $update_row->getRow()['u']['quantity']['raw'];

            $this->queries .= AccelaSearch\Query::getProductStockUpdateQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang, $quantity);
        }

        return $this;
    }

    public function getQueries(): string
    {
        return $this->queries;
    }
}

class PriceUpdate extends UpdateOperation implements Operation
{
    private $queries = '';

    public function __construct()
    {
        $this->setName('price');
    }

    public function generateQueries(UpdateRow $update_row, UpdateContext $context): self
    {
        $id_product = $context->id_product;
        $id_product_attribute = $context->id_product_attribute;

        if ($context->isGlobalOperation()) {
            $this->queries .= AccelaSearch\Query::getGlobalProductPriceUpdateQuery($context->id_shop, $context->id_lang, $context->as_shop_id);
            $update_row->unsetOperationIfExist('i');
            $update_row->unsetOperationIfExist('d');
            $update_row->unsetOperationIfExist('u');
        }

        if ($update_row->isInsertOperation()) {
            [
                'id_product' => $row_id_product,
                'id_product_attribute' => $row_id_product_attribute
            ] = $update_row->getRow()['i']['id_product']['raw'];

            $this->queries .= AccelaSearch\Query::getProductPriceUpdateQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang);
        }

        if ($update_row->isDeleteOperation()) {
            [
                'id_product' => $row_id_product,
                'id_product_attribute' => $row_id_product_attribute
            ] = $update_row->getRow()['d']['id_specific_price']['raw'];

            $this->queries .= AccelaSearch\Query::getProductPriceUpdateQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang);
        }

        if ($update_row->isUpdateOperation()) {
            [
                'id_product' => $row_id_product,
                'id_product_attribute' => $row_id_product_attribute
            ] = $update_row->getRow()['u']['id_product']['raw'];

            $this->queries .= AccelaSearch\Query::getProductPriceUpdateQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang);
        }

        return $this;
    }

    public function getQueries(): string
    {
        return $this->queries;
    }
}

class CategoryUpdate extends UpdateOperation implements Operation
{
    private $queries = '';

    public function __construct()
    {
        $this->setName('category');
    }

    public function generateQueries(UpdateRow $update_row, UpdateContext $context): self
    {
        $id_product = $context->id_product;
        $id_product_attribute = $context->id_product_attribute;

        if ($update_row->isDeleteOperation()) {
            $this->queries .= AccelaSearch\Query::getCategoryDeleteQuery($id_product, $context->id_shop, $context->id_lang, $context->as_shop_id);
            $update_row->unsetOperationIfExist('i');
            $update_row->unsetOperationIfExist('u');
        }

        if ($update_row->isInsertOperation()) {
            $update_row->unsetOperationIfExist('u');
            $this->queries .= AccelaSearch\Query::getCategoryCreationQuery($id_product, $context->id_shop, $context->id_lang, $context->as_shop_id);
        }

        if ($update_row->isUpdateOperation()) {
            $op_name = array_keys($update_row->getRow()['u'])[0];
            $new_value = $update_row->getRow()['u'][$op_name]['value'];
            $this->queries .= AccelaSearch\Query::getCategoryUpdateQuery($id_product, $new_value, $context->id_shop, $context->id_lang, $context->as_shop_id, $op_name);
        }

        return $this;
    }

    public function getQueries(): string
    {
        return $this->queries;
    }
}

class CategoryProductUpdate extends UpdateOperation implements Operation
{
    private $queries = '';

    public function __construct()
    {
        $this->setName('category_product');
    }

    public function generateQueries(UpdateRow $update_row, UpdateContext $context): self
    {
        $id_product = $context->id_product;
        $id_product_attribute = $context->id_product_attribute;

        if ($update_row->isDeleteOperation()) {
            foreach ($update_row->getRow()['d'] as $id_category_str => $cat_update) {
                [
                    'value' => $id_category,
                    'id_product' => $id_product
                ] = $cat_update['raw'];
                $lastupdate = date('Y-m-d H:i:s');
                $externalidstr = $context->buildExternalId([$id_category]);
                $ext_product_idstr = $context->buildExternalId([$id_product, 0]);
                $this->queries .= "UPDATE products_categories SET deleted = 1, lastupdate = '$lastupdate' WHERE productid = (SELECT id FROM products WHERE externalidstr = '$ext_product_idstr') AND categoryid = (SELECT id FROM categories WHERE externalidstr = '$externalidstr');";
            }
        }

        if ($update_row->isInsertOperation()) {
            foreach ($update_row->getRow()['i'] as $id_category_str => $cat_update) {
                [
                    'value' => $id_category,
                    'id_product' => $id_product
                ] = $cat_update['raw'];
                $lastupdate = date('Y-m-d H:i:s');
                $externalidstr = $context->buildExternalId([$id_category]);
                $ext_product_idstr = $context->buildExternalId([$id_product, 0]);
                $id_association = AS_Collector::getInstance()->getValue("SELECT id FROM products_categories WHERE productid = (SELECT id FROM products WHERE externalidstr = '$ext_product_idstr') AND categoryid = (SELECT id FROM categories WHERE externalidstr = '$externalidstr')");
                if (!$id_association) {
                    $this->queries .= "INSERT INTO products_categories (categoryid, productid) VALUES ((SELECT id FROM categories WHERE externalidstr = '$externalidstr'),(SELECT id FROM products WHERE externalidstr = '$ext_product_idstr'));";
                } else {
                    $this->queries .= "UPDATE products_categories SET deleted = 0, lastupdate = '$lastupdate' WHERE id = $id_association;";
                }
            }
        }

        return $this;
    }

    public function getQueries(): string
    {
        return $this->queries;
    }
}

class AttributeImageUpdate extends UpdateOperation implements Operation
{
    private $queries = '';

    public function __construct()
    {
        $this->setName('attribute_image');
    }

    public function generateQueries(UpdateRow $update_row, UpdateContext $context): self
    {
        $id_product = $context->id_product;
        $id_product_attribute = $context->id_product_attribute;

        if ($update_row->isInsertOperation()) {
            foreach ($update_row->getRow()['i'] as $id_image_str => $im_update) {
                [
                    'id_product' => $row_id_product,
                    'id_product_attribute' => $row_id_product_attribute,
                    'value' => $id_image
                ] = $im_update['raw'];

                $this->queries .= AccelaSearch\Query::getProductImageByIdQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang, $id_image);
            }
        }

        if ($update_row->isDeleteOperation()) {
            foreach ($update_row->getRow()['d'] as $id_image_str => $im_update) {
                [
                    'id_product' => $row_id_product,
                    'id_product_attribute' => $row_id_product_attribute,
                    'value' => $id_image
                ] = $im_update['raw'];

                $image_external_id_cover = $context->buildExternalId([$row_id_product, $row_id_product_attribute, $id_image, 'cover']);
                $image_external_id_others = $context->buildExternalId([$row_id_product, $row_id_product_attribute, $id_image, 'others']);
                $this->queries .= "UPDATE products_images SET deleted = 1 WHERE externalidstr = '$image_external_id_cover';";
                $this->queries .= "UPDATE products_images SET deleted = 1 WHERE externalidstr = '$image_external_id_others';";
            }
        }

        if ($update_row->isUpdateOperation()) {
            [
                'id_product' => $row_id_product,
                'id_product_attribute' => $row_id_product_attribute,
                'value' => $id_image
            ] = $update_row->getRow()['u']['id_image']['raw'];

            $this->queries .= AccelaSearch\Query::getProductImageByIdQuery($row_id_product, $row_id_product_attribute, $context->id_shop, $context->id_lang, $id_image);
        }

        return $this;
    }

    public function getQueries(): string
    {
        return $this->queries;
    }
}

class VariantUpdate extends UpdateOperation implements Operation
{
    private $queries = '';

    public function __construct()
    {
        $this->setName('variant_update');
    }

    public function generateQueries(UpdateRow $update_row, UpdateContext $context): self
    {
        $id_product = $context->id_product;
        $id_product_attribute = $context->id_product_attribute;

        if ($update_row->isInsertOperation()) {
            $this->queries .= AccelaSearch\Query::transformProductAndCreateVariant($id_product, $id_product_attribute, $context->id_shop, $context->id_lang, $context->as_shop_id);
        }

        if ($update_row->isDeleteOperation()) {
            $externalidstr = $context->buildExternalId([$id_product, $id_product_attribute]);
            $this->queries .= "UPDATE products SET deleted = 1 WHERE externalidstr = '$externalidstr';";
        }

        return $this;
    }

    public function getQueries(): string
    {
        return $this->queries;
    }
}

<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace AccelaSearch\Query;

use AccelaSearch\Collector;

/**
 * Create and manage SQL query
 *
 * Create a private class variable with raw query in HEREDOC format with this placeholder format: {{VAR_NAME}}
 * Call static function getByName with class variable name and data in array keys format replacement to get clean query with formatted data.
 */
class Query
{
  public static $query_data_manager;

  /**
   * Utilizzata in tutte le query "complesse" e viene istanziato dal controller prima di chiamare i metodi per generare le query
   *
   * @see AccelaSearch::createQueryDataInstanceByIdShopAndLang
   * @see AccelaSearch::generateProductsQueryByDifferentialRows
   * @see AccelaSearch::generateProductsQuery
   */
  public static function loadQueryData(QueryData $data)
  {
    self::$query_data_manager = $data;
  }

  public static function getPlaceholders(string $query)
  {
    preg_match_all('/\{{.*?\}}/', $query, $matches);
    $matches = $matches[0];
    $unique = array_unique($matches);
    sort($unique);

    return $unique;
  }

  /**
   * $data is formatted like:
   * ["var_name_to_replace" => "value"]
   * and automatically transformed to {{VAR_NAME_TO_REPLACE}} => value
   * NOTE: Db prefix is automatically added
   */
  public static function getByName(string $name, array $data): string
  {
    $qObj = (new self());
    if (!isset($qObj->{$name})) {
      throw new \Exception("Method $name doesnt exist in Query class, to perform a query with this name, create it first");
    }
    $query = $qObj->{$name};
    $placement = [];
    $placement['{{PREFIX}}'] = $qObj->db_prefix;
    foreach ($data as $place_name => $place_value) {
      $placement['{{' . strtoupper($place_name) . '}}'] = $place_value;
    }
    // launch an exception if not all placeholder of the query are present in $data array keys
    $placeholders = self::getPlaceholders($query);
    $placement_keys = array_keys($placement);
    foreach ($placeholders as $placeholder) {
      if (!in_array($placeholder, $placement_keys)) {
        throw new \Exception('Query placeholders doesnt match with passed parameters data');
      }
    }
    $cleaned_query = preg_replace("/\r|\n/", '', str_replace($placement_keys, array_values($placement), $query));
    if (\AccelaSearch::AS_CONFIG['LOG_QUERY'] === true) {
      \Db::getInstance()->insert('log', [
        'severity' => 1,
        'error_code' => 0,
        'message' => $name . ': ' . pSQL($cleaned_query),
      ]);
    }

    return $cleaned_query;
  }

  /**
   * Create a product by id_product
   *
   **/
  public static function getProductCreationQuery($id_product, $id_shop, $id_lang, $as_shop_id, $as_shop_real_id, $ignore = true)
  {
    $ps_product_query = self::getByName(
      'psProducts_query',
      [
        'id_shop' => $id_shop,
        'id_lang' => $id_lang,
        'limit' => '1',
        'add_mpn_if_exist' => '',
        'add_product_type_if_exist' => '',
        'single_product' => "AND p.id_product = $id_product",
      ]
    );
    $product = \Db::getInstance()->executeS($ps_product_query);
    if (count($product) == 0) {
      return;
    }
    $product = $product[0];
    $queries = [];
    \AccelaSearch::generateProductQueryByProductRow($id_shop, $id_lang, $as_shop_id, $as_shop_real_id, $product, $queries);
    $queries = implode('', $queries);
    if (!$ignore) {
      $queries = str_replace('INSERT IGNORE', 'INSERT', $queries);
    }

    return $queries;
  }

  public static function getProductUpdateQueryByEntity($update, $id_shop, $id_lang)
  {
    [
      'id_product' => $id_product,
      'id_product_attribute' => $id_product_attribute,
      'name' => $entity_name,
      'value' => $entity_value
    ] = $update;

    $attributes_to_table = [
      'name' => 'products_attr_str',
      'brand' => 'products_attr_str',
      'ean13' => 'products_attr_str',
      'isbn' => 'products_attr_str',
      'upc' => 'products_attr_str',
      'mpn' => 'products_attr_str',
      'short_description' => 'products_attr_text',
      'description' => 'products_attr_text',
      'sku' => 'products_attr_str',
    ];

    if ($entity_name == 'price') {
      $query = self::getProductPriceUpdateQuery($id_product, $id_product_attribute, $id_shop, $id_lang);

      return $query;
    }

    if ($entity_name == 'reference') {
      $externalidstr = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute;
      $timestamp = date('Y-m-d H:i:s');
      $query = "UPDATE products SET sku = '$entity_value', lastupdate = '$timestamp' WHERE externalidstr = '$externalidstr';";
      $table = $attributes_to_table['sku'];
      $entity_value = pSQL($entity_value);
      $externalidstr = $id_shop . '_' . $id_lang . '_' . $id_product . '_sku';
      $query .= "UPDATE $table SET value = '$entity_value', lastupdate = '$timestamp' WHERE externalidstr = '$externalidstr';";

      return $query;
    }

    if ($entity_name == 'active') {
      if (!(int) $id_product_attribute) {
        $id_product_attribute = '%';
      }
      $externalidstr = $id_shop . '_%_' . $id_product . '_' . $id_product_attribute;
      $timestamp = date('Y-m-d H:i:s');
      $deleted = !(int) $entity_value ? 1 : 0;
      $query = "UPDATE products SET deleted = '$deleted', lastupdate = '$timestamp' WHERE externalidstr LIKE '$externalidstr';";

      return $query;
    }

    // backward compatibility conversion
    $entity_name = ($entity_name == 'description_short') ? 'short_description' : $entity_name;

    $externalidstr = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $entity_name;

    if (!array_key_exists($entity_name, $attributes_to_table)) {
      return;
    }

    $table = $attributes_to_table[$entity_name];
    $entity_value = pSQL($entity_value);
    $timestamp = date('Y-m-d H:i:s');
    $query = "UPDATE $table SET value = '$entity_value', lastupdate = '$timestamp' WHERE externalidstr = '$externalidstr';";

    return $query;
  }

  public static function getProductImageByIdQuery($id_product, $id_product_attribute, $id_shop, $id_lang, $id_image)
  {
    $link = new \Link();
    $image_url = $link->getImageLink('product-image-' . rand(1, 99999), $id_image);
    $externalproductidstr = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute;
    $externalidstr = $externalproductidstr . '_' . $id_image . '_others';
    $externalidstr_cover = $externalproductidstr . '_' . $id_image . '_cover';

    [
      'others' => $others_id
    ] = self::$query_data_manager->as_attributes_ids;

    $image_id_association = Collector::getInstance()->executeS("SELECT id FROM products_images WHERE externalidstr = '$externalidstr' OR externalidstr = '$externalidstr_cover'");

    if (!(bool) count($image_id_association)) {
      return self::getByName('addImageToProductQuery', [
        'id_product' => $id_product,
        'product_external_id_str' => $externalproductidstr,
        'other_url' => \Tools::getShopProtocol() . $image_url,
        'others_url_idstr' => $externalidstr,
        'others_id' => $others_id,
      ]);
    } else {
      $queries = '';
      foreach ($image_id_association as $im_id_assoc) {
        $id = $im_id_assoc['id'];
        $queries .= "UPDATE products_images SET deleted = 0 WHERE id = $id;";
      }

      return $queries;
    }
  }

  public static function getProductImagesRegenerationQuery($id_product, $id_product_attribute, $id_shop, $id_lang)
  {
    return "Regenerate images query for product $id_product \n";
  }

  public static function transformProductAndCreateVariant($id_product, $id_product_attribute, $id_shop, $id_lang, $as_shop_id)
  {
    $queryData = Query::$query_data_manager;
    if (!$queryData) {
      throw new \Exception('Cannot perform product query without query data manager instance');
    }
    $queries = [];
    $externalidstr = $id_shop . '_' . $id_lang . '_' . $id_product . '_' . $id_product_attribute;
    $externalidstr_conf = $id_shop . '_' . $id_lang . '_' . $id_product . '_0';
    $queries[] = "UPDATE products SET typeid = 30 WHERE externalidstr = '$externalidstr_conf';";

    $childrens = \Db::getInstance()->executeS(self::getByName('getProductChildren_query', [
      'id_shop' => $id_shop,
      'id_product_attribute' => $id_product_attribute,
    ]));

    $as_product_types = $queryData->as_product_types;

    [
      'name' => $name_id,
      'short_description' => $short_description_id,
      'description' => $description_id,
      'brand' => $brand_id,
      'ean13' => $ean13_id,
      'isbn' => $isbn_id,
      'upc' => $upc_id,
      'mpn' => $mpn_id,
      'cover' => $cover_id,
      'others' => $others_id,
      'sku' => $sku_id
    ] = $queryData->as_attributes_ids;

    $as_categories = $queryData->as_categories;
    $warehouse_id = $queryData->warehouse_id;
    $customer_groups = $queryData->customer_groups;
    $users_groups = $queryData->users_groups;
    $link = $queryData->link;
    $currencies_cart = $queryData->currencies_cart;

    $url = $link->getProductLink($id_product, null, null, null, $id_lang, $id_shop);

    $ps_product = [
      'link_rewrite' => $childrens[0]['link_rewrite'],
    ];

    $queries[] = "SET @generated_product_id = (SELECT id FROM products WHERE externalidstr = '$externalidstr_conf');";

    \AccelaSearch::addChildrensQuery(
      $childrens,
      $id_shop,
      $id_lang,
      $id_product,
      $queries,
      $currencies_cart,
      $customer_groups,
      $as_product_types,
      $as_shop_id,
      $warehouse_id,
      $url,
      $ean13_id,
      $isbn_id,
      $upc_id,
      $mpn_id,
      $ps_product,
      $cover_id,
      $others_id,
      $users_groups,
      $sku_id
    );

    return implode('', $queries);
  }

  public static function getFeatureProductDeleteQuery($id_product, $id_shop, $id_lang, $id_feature_value)
  {
    $queryData = Query::$query_data_manager;
    if (!$queryData) {
      throw new \Exception('Cannot perform product query without query data manager instance');
    }
    $ps_feature_value = new \FeatureValue($id_feature_value);
    $ps_feature = new \Feature($ps_feature_value->id_feature);
    $name_feature = $ps_feature->name[$id_lang];
    $slug = strtolower(str_replace(' ', '_', $name_feature));
    $external_id_str = $id_shop . '_' . $id_lang . '_' . $id_product . '_0_' . $slug;
    $query = "UPDATE products_attr_str SET deleted = 1 WHERE externalidstr = '$external_id_str';";

    return $query;
  }

  public static function getFeatureProductInsertQuery($id_product, $id_shop, $id_lang, $id_feature_value)
  {
    $queryData = Query::$query_data_manager;
    if (!$queryData) {
      throw new \Exception('Cannot perform product query without query data manager instance');
    }
    $ps_feature_value = new \FeatureValue($id_feature_value);
    $ps_feature = new \Feature($ps_feature_value->id_feature);
    $name_feature = $ps_feature->name[$id_lang];
    if (!in_array($name_feature, $queryData->as_attributes_ids)) {
      $externalidstr = 'feature_0_' . $id_lang . '_' . $ps_feature_value->id_feature;
      $slug = strtolower(str_replace(' ', '_', $name_feature));
      $new_feature = Query::getByName(
        'createVariant_query',
        [
          'name' => \AccelaSearch::sanitize($name_feature),
          'storeview_id' => $queryData->as_shop_id,
          'external_id_str' => $externalidstr,
          'slug' => $slug,
        ]
      );
      Collector::getInstance()->query($new_feature);
      $as_shop_id = $queryData->as_shop_id;
      Query::$query_data_manager = null;
      \AccelaSearch::createQueryDataInstanceByIdShopAndLang($id_shop, $id_lang, $as_shop_id);
      $queryData = Query::$query_data_manager;
    }

    $label_id = $queryData->as_attributes_ids[$name_feature];
    $slug = strtolower(str_replace(' ', '_', $name_feature));
    $external_id_str = $id_shop . '_' . $id_lang . '_' . $id_product . '_0_' . $slug;
    $external_id_str_product = $id_shop . '_' . $id_lang . '_' . $id_product . '_0';
    $feature_value = $ps_feature_value->value[$id_lang];

    $query = Query::getByName('addFeature_query', [
      'label_id' => $label_id,
      'value' => $feature_value,
      'is_configurable' => 0,
      'external_id_str' => $external_id_str,
      'external_id_str_product' => $external_id_str_product,
    ]);

    $query .= "UPDATE products_attr_str SET deleted = 0 WHERE externalidstr = '$external_id_str';";
    $query .= "UPDATE products_attr_str SET value = '$feature_value' WHERE externalidstr = '$external_id_str';";

    return $query;
  }

  public static function getProductStockUpdateQuery($id_product, $id_product_attribute, $id_shop, $id_lang, $quantity)
  {
    $product_attribute_addition = (bool) $id_product_attribute ? '_' . $id_product_attribute : '_0';
    $externalidstr = $id_shop . '_' . $id_lang . '_' . $id_product . $product_attribute_addition;
    $timestamp = date('Y-m-d H:i:s');
    $query = "UPDATE stocks SET quantity = '$quantity', lastupdate = '$timestamp' WHERE productid = (SELECT id FROM products WHERE externalidstr = '$externalidstr');";

    return $query;
  }

  public static function getProductPriceUpdateQuery($id_product, $id_product_attribute, $id_shop, $id_lang)
  {
    $queries = [];

    $currencies_cart = self::$query_data_manager->currencies_cart;
    $customer_groups = self::$query_data_manager->customer_groups;
    $users_groups = self::$query_data_manager->users_groups;

    \AccelaSearch::addProductPriceToQueries(
      $id_shop,
      $id_lang,
      $currencies_cart,
      $customer_groups,
      $users_groups,
      $id_product,
      $id_product_attribute,
      $queries
    );

    return implode('', $queries);
  }

  public static function getGlobalProductPriceUpdateQuery($id_shop, $id_lang, $as_shop_id)
  {
    $queries = [];

    $ps_products_query = self::getByName(
      'getProductsForGlobalPriceUpdate',
      [
        'id_shop' => $id_shop,
      ]
    );

    $ps_products = \Db::getInstance()->executeS($ps_products_query);

    // processo i prezzi dei semplici o configurabili

    $currencies_cart = self::$query_data_manager->currencies_cart;
    $customer_groups = self::$query_data_manager->customer_groups;
    $users_groups = self::$query_data_manager->users_groups;

    foreach ($ps_products as $ps_product) {
      [
        'id_product' => $id_product
      ] = $ps_product;

      $id_product_attribute = 0;

      \AccelaSearch::addProductPriceToQueries(
        $id_shop,
        $id_lang,
        $currencies_cart,
        $customer_groups,
        $users_groups,
        $id_product,
        $id_product_attribute,
        $queries
      );
    }

    $ps_products_children_query = self::getByName(
      'getProductsChildrenForGlobalPriceUpdate',
      [
        'id_shop' => $id_shop,
      ]
    );

    $ps_products_variants = \Db::getInstance()->executeS($ps_products_children_query);

    // processo i prezzi delle varianti

    foreach ($ps_products_variants as $variant) {
      [
        'id_product' => $id_product,
        'id_product_attribute' => $id_product_attribute
      ] = $variant;

      \AccelaSearch::addProductPriceToQueries(
        $id_shop,
        $id_lang,
        $currencies_cart,
        $customer_groups,
        $users_groups,
        $id_product,
        $id_product_attribute,
        $queries
      );
    }

    $timestamp = date('Y-m-d H:i:s');
    $queries[] = "UPDATE products SET lastupdate = '$timestamp' WHERE siteid = $as_shop_id;";

    return implode('', $queries);
  }

  public static function getCategoryCreationQuery($id_category, $id_shop, $id_lang, $as_shop_id)
  {
    [
      'name' => $name,
      'id_parent' => $id_parent
    ] = \Db::getInstance()->getRow('SELECT cl.name, c.id_parent FROM ' . _DB_PREFIX_ . "category_lang AS cl JOIN ps_category AS c ON c.id_category = cl.id_category WHERE cl.id_category = $id_category AND cl.id_shop = $id_shop AND cl.id_lang = $id_lang");
    $link = new \Link();
    $full_name = \AccelaSearch::getFullCategoryNameByIdAndLang($id_category, $id_lang);
    $url = $link->getCategoryLink($id_category, null, $id_lang, null, $id_shop);
    $external_id_str = $id_shop . '_' . $id_lang . '_' . $id_category;
    $external_id_str_parent = $id_shop . '_' . $id_lang . '_' . $id_parent;

    return self::getByName('categoryCreation_query', [
      'storeviewid' => $as_shop_id,
      'categoryname' => $name,
      'fullcategoryname' => $full_name,
      'externalidstr' => $external_id_str,
      'externalidstr_parent' => $external_id_str_parent,
      'url' => $url,
    ]);
  }

  public static function getCategoryDeleteQuery($id_category, $id_shop, $id_lang, $as_shop_id)
  {
    $external_id_str = $id_shop . '_' . $id_lang . '_' . $id_category;

    return "UPDATE categories SET deleted = 1 WHERE externalidstr = '$external_id_str';";
  }

  public static function getCategoryUpdateQuery($id_category, $name, $id_shop, $id_lang, $as_shop_id, $op_name)
  {
    $queries = '';

    if (!(bool) $id_shop && !(bool) $id_lang) {
      $as_shops = \AccelaSearch::getAsShops();
    } else {
      $as_shops = [
        $id_shop . '_' . $id_lang => [
          'id_shop' => $id_shop,
          'id_lang' => $id_lang,
          'as_shop_id' => $as_shop_id,
        ],
      ];
    }

    foreach ($as_shops as $as_shop) {
      [
        'id_shop' => $id_shop,
        'id_lang' => $id_lang,
        'as_shop_id' => $as_shop_id
      ] = $as_shop;

      $external_id_str = $id_shop . '_' . $id_lang . '_' . $id_category;

      // è stato aggiornato anche il parent e quindi uno spostamento di genitore
      // comporta anche la riscrittura di tutta l'alberatura
      if ($op_name == 'id_parent') {
        $cat_external_id_str_parent = $id_shop . '_' . $id_lang . '_' . $name;
        Collector::getInstance()->query("UPDATE categories SET lastupdate = NOW(), parentid = (SELECT id FROM (select * from categories) as c WHERE c.externalidstr = '$cat_external_id_str_parent') WHERE externalidstr = '$external_id_str'");
      }

      if ($op_name == 'id_parent' || $op_name == 'link_rewrite') {
        $name = Collector::getInstance()->getValue("SELECT categoryname FROM categories WHERE externalidstr = '$external_id_str'");
      }

      $link = new \Link();
      $full_name = \AccelaSearch::getFullCategoryNameByIdAndLang($id_category, $id_lang);
      $url = $link->getCategoryLink($id_category, null, $id_lang, null, $id_shop);

      $has_children = Collector::getInstance()->executeS("SELECT * FROM categories WHERE parentid = (SELECT id FROM categories WHERE externalidstr = '$external_id_str')");

      $queries .= self::getByName('categoryUpdate_query', [
        'storeviewid' => $as_shop_id,
        'categoryname' => $name,
        'fullcategoryname' => $full_name,
        'externalidstr' => $external_id_str,
        'url' => $url,
      ]);

      while (count($has_children) > 0) {
        foreach ($has_children as $children) {
          [
            'id' => $id_category_children,
            'categoryname' => $name,
            'externalidstr' => $external_id_str
          ] = $children;
          [$id_shop, $id_lang, $ps_id_category] = explode('_', $external_id_str);
          $full_name = \AccelaSearch::getFullCategoryNameByIdAndLang($ps_id_category, $id_lang);
          $url = $link->getCategoryLink((int) $ps_id_category, null, (int) $id_lang, null, (int) $id_shop);
          $queries .= self::getByName('categoryUpdate_query', [
            'storeviewid' => $as_shop_id,
            'categoryname' => $name,
            'fullcategoryname' => $full_name,
            'externalidstr' => $external_id_str,
            'url' => $url,
          ]);
          $has_children = Collector::getInstance()->executeS("SELECT * FROM categories WHERE parentid = (SELECT id FROM categories WHERE externalidstr = '$external_id_str')");
        }
      }
    }

    return $queries;
  }

  /**
   * Name convention: When possible use the function name and add _query suffix.
   * Add queries here 🔽
   */
  public $db_prefix = _DB_PREFIX_;

  private $getProductChildren_query = <<<SQL
  SELECT pas.*, pa.*, pl.link_rewrite, sa.quantity AS real_qty
  FROM {{PREFIX}}product_attribute_shop AS pas
  JOIN {{PREFIX}}product_attribute AS pa ON pa.id_product_attribute = pas.id_product_attribute
  JOIN {{PREFIX}}product_lang AS pl on pl.id_product = pas.id_product
  JOIN {{PREFIX}}stock_available sa ON sa.id_product_attribute = pa.id_product_attribute
  WHERE pas.id_shop = {{ID_SHOP}}
  AND pas.id_product_attribute = {{ID_PRODUCT_ATTRIBUTE}}
  GROUP BY pas.id_product_attribute;
SQL;

  private $categoryUpdate_query = <<<SQL
  UPDATE categories
  SET
  categoryname = '{{CATEGORYNAME}}',
  fullcategoryname = '{{FULLCATEGORYNAME}}',
  url = '{{URL}}',
  lastupdate = NOW()
  WHERE externalidstr = '{{EXTERNALIDSTR}}';
SQL;

  private $categoryCreation_query = <<<SQL
  INSERT IGNORE INTO categories
  (
    parentid,
    storeviewid,
    categoryname,
    fullcategoryname,
    externalidstr,
    url
  )
  VALUES
  (
    (SELECT id_as FROM (SELECT id AS id_as FROM categories WHERE externalidstr = '{{EXTERNALIDSTR_PARENT}}') id_as),
    {{STOREVIEWID}},
    '{{CATEGORYNAME}}',
    '{{FULLCATEGORYNAME}}',
    '{{EXTERNALIDSTR}}',
    '{{URL}}'
  );
SQL;

  private $getProductsChildrenForGlobalPriceUpdate = <<<SQL
  SELECT pas.id_product, pas.id_product_attribute FROM {{PREFIX}}product_attribute_shop AS pas
  WHERE pas.id_shop = {{ID_SHOP}};
SQL;

  private $remote_product_delete = <<<SQL
    UPDATE products SET deleted = 1 WHERE externalidstr = '{{PRODUCT_EXTERNAL_ID_STR}}';
SQL;

  private $getProductsForGlobalPriceUpdate = <<<SQL
  SELECT ps.*
  FROM {{PREFIX}}product_shop AS ps
  JOIN {{PREFIX}}product AS p ON p.id_product = ps.id_product
  WHERE ps.id_shop = {{ID_SHOP}}
  AND p.active = 1
SQL;

  private $addVariant_query = <<<SQL
  INSERT IGNORE INTO products_attr_str
  (
    labelid,
    productid,
    value,
    configurable,
    externalidstr
  )
  VALUES
  (
    {{LABEL_ID}},
    @generated_product_id_children,
    '{{VALUE}}',
    {{IS_CONFIGURABLE}},
    '{{EXTERNAL_ID_STR}}'
  );
SQL;

  private $addFeature_query = <<<SQL
  INSERT IGNORE INTO products_attr_str
  (
    labelid,
    productid,
    value,
    configurable,
    externalidstr
  )
  VALUES
  (
    {{LABEL_ID}},
    (SELECT id FROM products WHERE externalidstr = '{{EXTERNAL_ID_STR_PRODUCT}}'),
    '{{VALUE}}',
    {{IS_CONFIGURABLE}},
    '{{EXTERNAL_ID_STR}}'
  );
SQL;

  private $mainProductsChildrenInsert_query = <<<SQL
  INSERT IGNORE INTO products
  (
    sku,
    siteid,
    typeid,
    externalidstr,
    url
  )
  VALUES
  (
    {{SKU}},
    '{{AS_SHOP_ID}}',
    '{{TYPEID}}',
    '{{PRODUCT_EXTERNAL_ID_STR}}',
    '{{URL}}'
  );

  SET @generated_product_id_children = LAST_INSERT_ID();

  INSERT IGNORE INTO products_children
  (
    productid,
    parentid
  )
  VALUES
  (
    @generated_product_id_children,
    @generated_product_id
  );

  INSERT IGNORE INTO products_attr_str (labelid, productid, value, externalidstr)
  VALUES
  (
    '{{EAN13_ID}}',
    @generated_product_id_children,
    '{{EAN13}}',
    '{{EAN13_EXTERNAL}}'
  );

  INSERT IGNORE INTO products_attr_str (labelid, productid, value, externalidstr)
  VALUES
  (
    '{{ISBN_ID}}',
    @generated_product_id_children,
    '{{ISBN}}',
    '{{ISBN_EXTERNAL}}'
  );

  INSERT IGNORE INTO products_attr_str (labelid, productid, value, externalidstr)
  VALUES
  (
    '{{UPC_ID}}',
    @generated_product_id_children,
    '{{UPC}}',
    '{{UPC_EXTERNAL}}'
  );

  INSERT IGNORE INTO products_attr_str (labelid, productid, value, externalidstr)
  VALUES
  (
    '{{MPN_ID}}',
    @generated_product_id_children,
    '{{MPN}}',
    '{{MPN_EXTERNAL}}'
  );

  INSERT IGNORE INTO products_attr_str (labelid, productid, value, externalidstr)
  VALUES
  (
    '{{SKU_ID}}',
    @generated_product_id_children,
    {{SKU}},
    '{{SKU_EXTERNAL}}'
  );

  INSERT IGNORE INTO stocks
  (
    wharehouseid,
    productid,
    quantity
  )
  VALUES
  (
    {{WAREHOUSE_ID}},
    @generated_product_id_children,
    '{{QTY}}'
  );
SQL;

  private $getProductChildrensById_query = <<<SQL
  SELECT pa.*, sa.quantity AS real_qty
  FROM {{PREFIX}}product_attribute pa
  JOIN {{PREFIX}}product_attribute_shop pas ON pas.id_product_attribute = pa.id_product_attribute
  JOIN {{PREFIX}}stock_available sa ON sa.id_product_attribute = pa.id_product_attribute
  WHERE 1
  AND pas.id_shop = {{ID_SHOP}}
  AND pa.id_product = {{ID_PRODUCT}}
  AND sa.id_shop = {{ID_SHOP}};
SQL;

  private $mainProductsInsertChildrenImagesOthers_query = <<<SQL
INSERT IGNORE INTO products_images (productid, externalproductidstr, labelid, sort, url, externalidstr)
VALUES
(@generated_product_id_children, '{{PRODUCT_EXTERNAL_ID_STR}}', {{OTHERS_ID}}, {{SORT}}, '{{OTHER_URL}}', '{{OTHERS_URL_IDSTR}}');
SQL;

  private $mainProductsInsertChildrenImageCover_query = <<<SQL
INSERT IGNORE INTO products_images (productid, externalproductidstr, labelid, sort, url, externalidstr)
VALUES
(@generated_product_id_children, '{{PRODUCT_EXTERNAL_ID_STR}}', {{COVER_ID}}, 1, '{{COVER_URL}}', '{{EXTERNAL_ID_STR}}');
SQL;

  private $addImageToProductQuery = <<<SQL
  SET @image_product_id = (SELECT id FROM products WHERE externalidstr = '{{PRODUCT_EXTERNAL_ID_STR}}');
  INSERT IGNORE INTO products_images (productid, externalproductidstr, labelid, sort, url, externalidstr)
  VALUES
  (
    @image_product_id,
    '{{PRODUCT_EXTERNAL_ID_STR}}',
    {{OTHERS_ID}},
    (SELECT MAX(pis.sort) FROM (select * from products_images) as pis WHERE pis.productid = @image_product_id),
    '{{OTHER_URL}}',
    '{{OTHERS_URL_IDSTR}}'
  );
SQL;

  private $mainProductsInsertImagesOthers_query = <<<SQL
  INSERT IGNORE INTO products_images (productid, externalproductidstr, labelid, sort, url, externalidstr)
  VALUES
  (@generated_product_id, '{{PRODUCT_EXTERNAL_ID_STR}}', {{OTHERS_ID}}, {{SORT}}, '{{OTHER_URL}}', '{{OTHERS_URL_IDSTR}}');
SQL;

  private $mainProductsInsertImageCover_query = <<<SQL
  INSERT IGNORE INTO products_images (productid, externalproductidstr, labelid, sort, url, externalidstr)
  VALUES
  (@generated_product_id, '{{PRODUCT_EXTERNAL_ID_STR}}', {{COVER_ID}}, 1, '{{COVER_URL}}', '{{EXTERNAL_ID_STR}}');
SQL;

  private $shopInitializationsCustomerGroup_query = <<<SQL
  INSERT IGNORE INTO users_groups (label, externalidstr, storeviewid) VALUES ('{{NAME}}', '{{EXTERNALIDSTR}}', {{STOREVIEW_ID}});
SQL;

  private $createVariant_query = <<<SQL
  INSERT IGNORE INTO products_attr_label (label, storeviewid, externalidstr) VALUES ('{{NAME}}', {{STOREVIEW_ID}}, '{{EXTERNAL_ID_STR}}');
    SET @{{SLUG}}_label_id = LAST_INSERT_ID();
SQL;

  private $shopInitializationsAttributes_query = <<<SQL
  INSERT IGNORE INTO products_attr_label (label, storeviewid) VALUES ('name', {{STOREVIEW_ID}});
  SET @name_label_id = LAST_INSERT_ID();
  INSERT IGNORE INTO products_attr_label (label, storeviewid) VALUES ('short_description', {{STOREVIEW_ID}});
  SET @short_description_label_id = LAST_INSERT_ID();
  INSERT IGNORE INTO products_attr_label (label, storeviewid) VALUES ('description', {{STOREVIEW_ID}});
  SET @description_label_id = LAST_INSERT_ID();
  INSERT IGNORE INTO products_attr_label (label, storeviewid) VALUES ('brand', {{STOREVIEW_ID}});
  SET @brand_label_id = LAST_INSERT_ID();
  INSERT IGNORE INTO products_attr_label (label, storeviewid) VALUES ('ean13', {{STOREVIEW_ID}});
  SET @ean13_label_id = LAST_INSERT_ID();
  INSERT IGNORE INTO products_attr_label (label, storeviewid) VALUES ('isbn', {{STOREVIEW_ID}});
  SET @isbn_label_id = LAST_INSERT_ID();
  INSERT IGNORE INTO products_attr_label (label, storeviewid) VALUES ('upc', {{STOREVIEW_ID}});
  SET @upc_label_id = LAST_INSERT_ID();
  INSERT IGNORE INTO products_attr_label (label, storeviewid) VALUES ('mpn', {{STOREVIEW_ID}});
  SET @mpn_label_id = LAST_INSERT_ID();
  INSERT IGNORE INTO products_attr_label (label, storeviewid) VALUES ('sku', {{STOREVIEW_ID}});
  SET @sku_id = LAST_INSERT_ID();
  INSERT IGNORE INTO products_images_lbl (label, storeviewid, externalidstr, deleted) VALUES ('cover', {{STOREVIEW_ID}}, '{{EXTERNALIDSTR_WAREHOUSE}}', 0);
  INSERT IGNORE INTO products_images_lbl (label, storeviewid, externalidstr, deleted) VALUES ('others', {{STOREVIEW_ID}}, '{{EXTERNALIDSTR_WAREHOUSE}}', 0);
  INSERT IGNORE INTO warehouses
    (storeviewid, label, externalidstr, isvirtual)
  VALUES
    ({{STOREVIEW_ID}}, (SELECT description FROM storeviews WHERE id = {{STOREVIEW_ID}}), '{{EXTERNALIDSTR_WAREHOUSE}}', 1);
SQL;

  private $assignProductCategory_query = <<<SQL
  INSERT IGNORE INTO products_categories
  (
    categoryid,
    productid
  )
  VALUES
  (
    {{AS_CAT_ID}},
    @generated_product_id
  );
SQL;

  private $generateCategories_query = <<<SQL
  INSERT IGNORE INTO categories
  (
    parentid,
    storeviewid,
    categoryname,
    fullcategoryname,
    externalidstr,
    url
  )
  VALUES
  (
    {{ID_PARENT}},
    {{STOREVIEW_ID}},
    '{{NAME}}',
    '{{FULL_NAME}}',
    '{{EXTERNAL_ID_STR}}',
    '{{URL}}'
  );
SQL;

  private $generateParentCategory_query = <<<SQL
INSERT IGNORE INTO categories
(
  parentid,
  storeviewid,
  categoryname,
  fullcategoryname,
  externalidstr,
  url
)
VALUES
(
  {{ID_PARENT_CATEGORY}},
  {{STOREVIEW_ID}},
  '{{NAME}}',
  '{{FULL_NAME}}',
  '{{EXTERNAL_ID_STR}}',
  '{{URL}}'
);
SET @parent_id = LAST_INSERT_ID();
SQL;

  private $getFullCategoryNameByIdAndLang_query = <<<SQL
  SELECT c.*, cl.*
  FROM {{PREFIX}}category c
  JOIN {{PREFIX}}category_lang cl ON cl.id_category = c.id_category
  WHERE cl.id_lang = {{ID_LANG}} AND c.id_category = {{ID_CATEGORY}}
SQL;

  private $getCategoriesByIdShopAndLang_query = <<<SQL
  SELECT c.*, cl.*, cs.*
  FROM {{PREFIX}}category c
  JOIN {{PREFIX}}category_lang cl ON cl.id_category = c.id_category
  JOIN {{PREFIX}}category_shop cs ON cs.id_category = cl.id_category
  WHERE cl.id_shop = {{ID_SHOP}} AND cl.id_lang = {{ID_LANG}} AND cs.id_shop = {{ID_SHOP}}
SQL;

  private $priceInsert_query = <<<SQL
  INSERT IGNORE INTO prices
  (
    groupid,
    currency,
    price,
    specialprice,
    productid,
    externalproductid,
    externalproductidstr
  )
  VALUES
  (
    '{{AS_ID_GROUP}}',
    '{{CURRENCY}}',
    {{PRICE}},
    {{SPECIALPRICE}},
    @generated_product_id,
    {{ID_PRODUCT}},
    '{{PRODUCT_PRICE_EXTERNALIDSTR}}'
  );
SQL;

  private $priceUpdate_query = <<<SQL
  UPDATE prices
  SET
  price = {{PRICE}},
  specialprice = {{SPECIALPRICE}}
  WHERE externalproductidstr = '{{PRODUCT_PRICE_EXTERNALIDSTR}}'
  AND groupid = {{AS_ID_GROUP}}
  AND currency = '{{CURRENCY}}';
SQL;

  private $priceInsertChildren_query = <<<SQL
INSERT IGNORE INTO prices
(
  groupid,
  currency,
  price,
  specialprice,
  productid,
  externalproductid,
  externalproductidstr
)
VALUES
(
  '{{AS_ID_GROUP}}',
  '{{CURRENCY}}',
  {{PRICE}},
  {{SPECIALPRICE}},
  @generated_product_id_children,
  {{ID_PRODUCT}},
  '{{PRODUCT_PRICE_EXTERNALIDSTR}}'
);
SQL;

  /**
   * Variables
   *
   * sku, as_shop_id, typeid, product_external_id_str, url, name_id,
   * product_name, name_external, short_description_id, product_short_description,
   * short_description_external, description_id, product_description,
   * description_external, brand_id, brand, brand_external, warehouse_id, qty
   */
  private $mainProductsInsert_query = <<<SQL
	INSERT IGNORE INTO products
	(
		sku,
		siteid,
		typeid,
		externalidstr,
		url
	)
	VALUES
	(
		'{{SKU}}',
		'{{AS_SHOP_ID}}',
		'{{TYPEID}}',
		'{{PRODUCT_EXTERNAL_ID_STR}}',
		'{{URL}}'
	);

	SET @generated_product_id = LAST_INSERT_ID();

	INSERT IGNORE INTO products_attr_str
	(
		labelid,
		productid,
		value,
		externalidstr
	)
	VALUES
	(
		'{{NAME_ID}}',
		@generated_product_id,
		'{{PRODUCT_NAME}}',
		'{{NAME_EXTERNAL}}'
	);

  INSERT IGNORE INTO products_attr_str
	(
		labelid,
		productid,
		value,
		externalidstr
	)
	VALUES
	(
		'{{EAN13_ID}}',
		@generated_product_id,
		'{{EAN13}}',
		'{{EAN13_EXTERNAL}}'
	);

  INSERT IGNORE INTO products_attr_str (labelid, productid, value, externalidstr)
  VALUES
  (
    '{{ISBN_ID}}',
    @generated_product_id,
    '{{ISBN}}',
    '{{ISBN_EXTERNAL}}'
  );

  INSERT IGNORE INTO products_attr_str (labelid, productid, value, externalidstr)
  VALUES
  (
    '{{UPC_ID}}',
    @generated_product_id,
    '{{UPC}}',
    '{{UPC_EXTERNAL}}'
  );

  INSERT IGNORE INTO products_attr_str (labelid, productid, value, externalidstr)
  VALUES
  (
    '{{MPN_ID}}',
    @generated_product_id,
    '{{MPN}}',
    '{{MPN_EXTERNAL}}'
  );

  INSERT IGNORE INTO products_attr_str (labelid, productid, value, externalidstr)
  VALUES
  (
    '{{SKU_ID}}',
    @generated_product_id,
    '{{SKU}}',
    '{{SKU_EXTERNAL}}'
  );

	INSERT IGNORE INTO products_attr_text
	(
		labelid,
		productid,
		value,
		externalidstr
	)
	VALUES
	(
		'{{SHORT_DESCRIPTION_ID}}',
		@generated_product_id,
		'{{PRODUCT_SHORT_DESCRIPTION}}',
		'{{SHORT_DESCRIPTION_EXTERNAL}}'
	);

	INSERT IGNORE INTO products_attr_text
	(
		labelid,
		productid,
		value,
		externalidstr
	)
	VALUES
	(
		'{{DESCRIPTION_ID}}',
		@generated_product_id,
		'{{PRODUCT_DESCRIPTION}}',
		'{{DESCRIPTION_EXTERNAL}}'
	);

	INSERT IGNORE INTO products_attr_str
	(
		labelid,
		productid,
		value,
		externalidstr
	)
	VALUES
	(
		'{{BRAND_ID}}',
		@generated_product_id,
		'{{BRAND}}',
		'{{BRAND_EXTERNAL}}'
	);

	INSERT IGNORE INTO stocks
	(
		wharehouseid,
		productid,
		quantity
	)
	VALUES
	(
		{{WAREHOUSE_ID}},
		@generated_product_id,
		'{{QTY}}'
	);
SQL;

  private $getProductsChildren_query = <<<SQL
  SELECT pa.*, pas.*, sa.quantity AS real_qty
  FROM {{PREFIX}}product_attribute pa
  JOIN {{PREFIX}}product_attribute_shop pas ON pas.id_product_attribute = pa.id_product_attribute
  JOIN {{PREFIX}}stock_available sa ON sa.id_product_attribute = pa.id_product_attribute
  WHERE 1
  AND pas.id_shop = {{ID_SHOP}}
  AND pa.id_product = {{ID_PRODUCT}}
SQL;

  private $getProductTypeById_query = <<<SQL
  SELECT COUNT(pa.id_product)
  FROM {{PREFIX}}product_attribute pa
  JOIN {{PREFIX}}product_attribute_shop pas ON pas.id_product = pa.id_product
  WHERE 1
  AND pa.id_product = {{ID_PRODUCT}}
  AND pas.id_shop = {{ID_SHOP}}
  GROUP BY pa.id_product_attribute
SQL;

  private $psProducts_query = <<<SQL
  SELECT
    p.id_product,
    p.id_manufacturer,
    p.price,
    p.reference,
    {{ADD_MPN_IF_EXIST}}
    p.ean13,
    p.isbn,
    p.upc,
    pl.description,
    pl.description_short,
    pl.name,
    pl.link_rewrite,
    m.name AS brand,
    {{ADD_PRODUCT_TYPE_IF_EXIST}}
    sa.quantity
  FROM {{PREFIX}}product p
  JOIN {{PREFIX}}product_shop ps ON ps.id_product = p.id_product
  JOIN {{PREFIX}}product_lang pl ON pl.id_product = p.id_product
  LEFT JOIN {{PREFIX}}manufacturer m ON m.id_manufacturer = p.id_manufacturer
  LEFT JOIN {{PREFIX}}stock_available sa ON sa.id_product = p.id_product
  WHERE 1
  AND	ps.id_shop = {{ID_SHOP}}
  AND pl.id_lang = {{ID_LANG}}
  AND pl.id_shop = {{ID_SHOP}}
  AND sa.id_shop = {{ID_SHOP}}
  {{SINGLE_PRODUCT}}
  AND ps.active = 1
  GROUP BY p.id_product
  ORDER BY p.id_product DESC
  LIMIT {{LIMIT}}
SQL;

  private $estimateNbProducts_query = <<<SQL
  SELECT COUNT(p.id_product)
  FROM {{PREFIX}}product p
  JOIN {{PREFIX}}product_shop ps ON ps.id_product = p.id_product
  JOIN {{PREFIX}}product_lang pl ON pl.id_product = p.id_product
  WHERE 1
  AND	ps.id_shop = {{ID_SHOP}}
  AND pl.id_shop = {{ID_SHOP}}
  AND pl.id_lang = {{ID_LANG}}
  AND ps.active = 1
SQL;
}

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



namespace AccelaSearch\Trigger;

/**
 * Create and manage Triggers
 */
class Trigger
{
    private $triggerDataInstance;

    public function __construct(TriggerData $data)
    {
        $this->triggerDataInstance = $data;
    }

    /**
     * Get raw query fields to put inside trigger declaration
     *
     * @see field structure destructured inside the function
     */
    public function getQueryFields($fields, $type)
    {
        $queries = '';
        foreach ($fields as $field) {
            // field structure
            [
                'name' => $name, // name of the field, like "description" or "manufacturer"
                'select_fields' => $select_fields, // array of select field to put inside the insert
                'from_clause' => $select_from, // raw from
                'joins' => $joins, // array of raw joins
                'wheres' => $wheres // array of raw wheres
            ] = $field;

            $select_fields = implode(',', $select_fields);

            [
                'from_name' => $from_name,
                'from_as' => $from_as
            ] = $select_from;

            $joins = implode('', $joins);
            $wheres = implode('', $wheres);

            $if_structure = ($type == 'UPDATE') ? "IF NOT NEW.`{$name}`<=>OLD.`{$name}` THEN" : '';
            $end_if_structure = ($type == 'UPDATE') ? 'END IF;' : '';

            $table_attributes = [
                'product_attribute',
                'product_attribute_shop',
                'product_attribute_image',
                'stock_available',
                'specific_price',
            ];

            $table_attribute_field = (in_array($this->triggerDataInstance->table, $table_attributes)) ? ' `id_product_attribute`,' : '';

            $limit = ($type == 'DELETE') ? 'LIMIT 1' : '';

            $from = '';
            $where_base = '';

            if (!empty($from_name) && !empty($from_as)) {
                $from = "FROM `{{PREFIX}}$from_name` AS $from_as";
                $where_base = 'WHERE 1';
            }

            $queries .= <<<FIELD
      {$if_structure}
        INSERT INTO `{{PREFIX}}as_notifications`
        (
          `id_product`,
          {$table_attribute_field}
          `type`,
          `id_shop`,
          `id_lang`,
          `name`,
          `value`,
          `tblname`,
          `op`
        )
        (
          SELECT
          {$select_fields}
          {$from}
          {$joins}
          {$where_base}
          {$wheres}
          {$limit}
        );
      {$end_if_structure}
FIELD;
        }

        return $queries;
    }

    public static function getDeleteQueries($trigger_data)
    {
        $del_queries = '';
        foreach ($trigger_data as $trigger) {
            $trigger_name = $trigger['table'] . '_' . strtolower($trigger['type']);
            $del_queries .= 'DROP TRIGGER IF EXISTS `as_' . _DB_PREFIX_ . $trigger_name . '`;';
        }

        return $del_queries;
    }

    public function getQuery()
    {
        if (!$this->triggerDataInstance) {
            throw new \Exception('No trigger instance defined');
        }
        $fields = $this->getQueryFields($this->triggerDataInstance->fields, $this->triggerDataInstance->type);
        $trigger_name = $this->triggerDataInstance->table . '_' . strtolower($this->triggerDataInstance->type);
        $query = <<<TRIGGER
    CREATE TRIGGER `as_{{PREFIX}}{$trigger_name}`
      {$this->triggerDataInstance->when} {$this->triggerDataInstance->type} ON `{{PREFIX}}{$this->triggerDataInstance->table}` FOR EACH ROW
      BEGIN
        {$fields}
      END;
TRIGGER;
        $query = str_replace('{{PREFIX}}', _DB_PREFIX_, $query);

        return $query;
    }
}

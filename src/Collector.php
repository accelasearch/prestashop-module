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

namespace AccelaSearch;

class Collector
{
    private static $instance = null;
    private $pdo;
    private $result;

    private function __construct($credentials)
    {
        try {
            $dsn = 'mysql:host=' . $credentials->hostname . ';dbname=' . $credentials->name;
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 5,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ];

            $this->pdo = new \PDO(
                $dsn,
                $credentials->username,
                $credentials->password,
                $options
            );
        } catch (\PDOException $e) {
            echo 'MySql Connection Error: ' . $e->getMessage();
        }
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            $credentials = \Configuration::get('ACCELASEARCH_COLLECTOR');
            if (empty($credentials)) {
                throw new \Exception('Cannot connect to collector without credentials');
            }
            $credentials = json_decode($credentials);
            self::$instance = new Collector($credentials);
        }

        return self::$instance;
    }

    public function executeS($sql)
    {
        $q = $this->pdo->prepare($sql);
        if ($q->execute()) {
            $this->result = $q->fetchAll();
        }

        return $this->result;
    }

    public function getValue($sql)
    {
        $q = $this->pdo->prepare($sql);
        if ($q->execute()) {
            $this->result = $q->fetchColumn();
        }

        return $this->result;
    }

    public function insert($table, $data, $ignore = false)
    {
        $params = implode(',', array_keys($data));
        $values = array_values($data);
        $bind = implode(
            ',',
            array_map(
                function ($val) {
                    return ':' . $val;
                },
                array_keys($data)
            )
        );

        $ignore = $ignore === true ? 'IGNORE' : '';

        $raw_query = 'INSERT ' . $ignore . ' INTO ' . $table . ' (' . $params . ') VALUES (' . $bind . ')';

        if (\AccelaSearch::AS_CONFIG['LOG_QUERY'] === true) {
            \Db::getInstance()->insert('log', [
                'severity' => 1,
                'error_code' => 0,
                'message' => pSQL($this->interpolateQuery($raw_query, $data)),
            ]);
        }

        $q = $this->pdo->prepare($raw_query);
        foreach ($data as $k => &$value) {
            $q->bindParam($k, $value);
        }
        $q->execute();

        return $this->pdo->lastInsertId();
    }

    private function interpolateQuery($query, $params)
    {
        $keys = [];
        $values = $params;
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:' . $key . '/';
            } else {
                $keys[] = '/[?]/';
            }
            if (is_string($value)) {
                $values[$key] = "'" . $value . "'";
            }
            if (is_array($value)) {
                $values[$key] = "'" . implode("','", $value) . "'";
            }
            if (is_null($value)) {
                $values[$key] = 'NULL';
            }
        }
        $query = preg_replace($keys, $values, $query);

        return $query;
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollBack()
    {
        $this->pdo->rollBack();
    }

    public function exec($queries)
    {
        $this->pdo->exec($queries);
    }

    public function query($query)
    {
        $q = $this->pdo->prepare($query);
        if (\AccelaSearch::AS_CONFIG['LOG_QUERY'] === true) {
            \Db::getInstance()->insert('log', [
                'severity' => 1,
                'error_code' => 0,
                'message' => 'FULL_QUERY: ' . pSQL($query),
            ]);
        }

        return $q->execute();
    }

    public function errorInfo()
    {
        return $this->pdo->errorInfo();
    }

    public function update($table, $data, $where)
    {
        $params = array_keys($data);
        $values = array_values($data);
        $setSql = [];
        if (count($params) == count($values)) {
            for ($i = 0; $i < count($params); ++$i) {
                $setSql[] = $params[$i] . "='" . $values[$i] . "'";
            }
        }
        $setSql = implode(',', $setSql);
        $q = $this->pdo->prepare('UPDATE ' . $table . ' SET ' . $setSql . ' WHERE ' . $where);

        return $q->execute();
    }

    public function delete($table, $where)
    {
        $q = $this->pdo->prepare('DELETE FROM ' . $table . ' WHERE ' . $where);

        return $q->execute();
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
}

<?php
/*
 *   Created on Tue Oct 31 2023
 *   Copyright (c) 2023 BitsHost
 *   All rights reserved.
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *   SOFTWARE.
 *   Here you may host your app for free:
 *   https://bitshost.biz/
 */

namespace App\Common\Bmvc;

use App\Etc\Database;
use PDO;
use \RedBeanPHP\R;

/**
 * BaseModelOrm — RedBeanPHP helpers with identifier / fillable guards.
 *
 * Same rules as BaseModel: safe table/column names, optional $fillable,
 * no HTML escaping in the model layer.
 */
class BaseModelOrm
{
    /**
     * @var PDO|null
     */
    protected $conn;

    /**
     * @var self|null
     */
    private static $instance = null;

    /**
     * @var PDO|null
     */
    private static $connection = null;

    private static bool $beanReady = false;

    /**
     * @var array<string, mixed>
     */
    private $cache = [];

    /**
     * Mass-assignment whitelist. Empty = any safe identifier key allowed.
     *
     * @var list<string>
     */
    protected array $fillable = [];

    private const MAX_PAGE_SIZE = 500;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (self::$connection === null) {
            self::$connection = (new Database())->getConnection();
        }
        $this->conn = self::$connection;

        if (!self::$beanReady) {
            $this->readBeanPhp();
            self::$beanReady = true;
        }
    }

    /**
     * Initialize RedBeanPHP
     */
    public function readBeanPhp()
    {
        R::setup($this->conn);
    }

    /**
     * Create a single record
     *
     * @param array<string, mixed> $data
     * @return int
     */
    public function create(array $data, string $table)
    {
        $data = $this->filterAssignable($data);
        $table = $this->assertIdentifier($table);
        if ($data === []) {
            return 0;
        }

        R::begin();
        try {
            $bean = R::dispense($table);
            foreach ($data as $key => $value) {
                $bean->$key = $value;
            }

            $id = R::store($bean);
            R::commit();
            return (int) $id;
        } catch (\Exception $e) {
            R::rollback();
            throw $e;
        }
    }

    /**
     * Create multiple records in batch
     *
     * @param list<array<string, mixed>> $dataSet
     * @return list<int>
     */
    public function createBatch(array $dataSet, string $table)
    {
        $table = $this->assertIdentifier($table);
        if ($dataSet === []) {
            return [];
        }

        $ids = [];
        R::begin();
        try {
            foreach ($dataSet as $data) {
                $data = $this->filterAssignable($data);
                if ($data === []) {
                    continue;
                }
                $bean = R::dispense($table);
                foreach ($data as $key => $value) {
                    $bean->$key = $value;
                }
                $ids[] = (int) R::store($bean);
            }
            R::commit();
            return $ids;
        } catch (\Exception $e) {
            R::rollback();
            throw $e;
        }
    }

    /**
     * Read all records from a table
     *
     * @return list<array<string, mixed>>
     */
    public function readAll(string $table)
    {
        $table = $this->assertIdentifier($table);
        $cacheKey = "all_{$table}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $result = R::getAll('SELECT * FROM ' . $this->quoteIdentifier($table));
        $this->cache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Read a single record
     *
     * @return list<array<string, mixed>>
     */
    public function read(int $id, string $table)
    {
        $table = $this->assertIdentifier($table);
        $cacheKey = "{$table}_{$id}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $result = R::getAll(
            'SELECT * FROM ' . $this->quoteIdentifier($table) . ' WHERE `id` = :id',
            [':id' => $id]
        );

        $this->cache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Read records with pagination
     *
     * @return array{data: mixed, total: int, current_page: int, per_page: int, total_pages: float|int, has_more: bool}
     */
    public function readWithPagination(string $table, int $page = 1, int $pageSize = 3)
    {
        $table = $this->assertIdentifier($table);
        $page = max(1, $page);
        $pageSize = max(1, min(self::MAX_PAGE_SIZE, $pageSize));
        $offset = ($page - 1) * $pageSize;

        $cacheKey = "{$table}_page_{$page}_size_{$pageSize}";

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        R::begin();
        try {
            $total = R::count($table);
            $totalPages = $pageSize > 0 ? (int) ceil($total / $pageSize) : 0;

            $records = R::getAll(
                'SELECT * FROM ' . $this->quoteIdentifier($table) . ' LIMIT :offset, :pageSize',
                [
                    ':offset' => $offset,
                    ':pageSize' => $pageSize
                ]
            );

            $result = [
                'data' => $records,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $pageSize,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages
            ];

            R::commit();
            $this->cache[$cacheKey] = $result;

            return $result;
        } catch (\Exception $e) {
            R::rollback();
            throw $e;
        }
    }

    /**
     * Read paginated records with additional options.
     *
     * Options:
     * - where: associative column => value (AND equality; values bound)
     * - orderBy: column name string, or ['column' => string, 'direction' => ASC|DESC]
     *
     * @param array{where?: array<string, mixed>, orderBy?: string|array{column?: string, direction?: string}} $options
     * @return array<string, mixed>
     */
    public function readWithAdvancedPagination(string $table, int $page = 1, int $pageSize = 10, array $options = [])
    {
        $tableSql = $this->quoteIdentifier($table);
        $page = max(1, $page);
        $pageSize = max(1, min(self::MAX_PAGE_SIZE, $pageSize));
        $offset = ($page - 1) * $pageSize;

        $bindings = [];
        $whereParts = [];
        if (!empty($options['where']) && is_array($options['where'])) {
            $i = 0;
            foreach ($options['where'] as $col => $value) {
                if (!is_string($col) || !$this->isSafeIdentifier($col)) {
                    throw new \InvalidArgumentException('Invalid WHERE column: ' . (string) $col);
                }
                $param = ':w' . $i++;
                $whereParts[] = $this->quoteIdentifier($col) . ' = ' . $param;
                $bindings[$param] = $value;
            }
        }
        $whereSql = $whereParts !== [] ? 'WHERE ' . implode(' AND ', $whereParts) : '';

        $orderSql = '';
        if (!empty($options['orderBy'])) {
            $orderCol = '';
            $direction = 'ASC';
            if (is_array($options['orderBy'])) {
                $orderCol = (string) ($options['orderBy']['column'] ?? '');
                $direction = strtoupper((string) ($options['orderBy']['direction'] ?? 'ASC'));
            } else {
                $orderCol = (string) $options['orderBy'];
            }
            if (!in_array($direction, ['ASC', 'DESC'], true)) {
                $direction = 'ASC';
            }
            $orderSql = 'ORDER BY ' . $this->quoteIdentifier($orderCol) . ' ' . $direction;
        }

        $cacheKey = "{$table}_page_{$page}_size_{$pageSize}_" . md5(json_encode($options));

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        R::begin();
        try {
            $totalQuery = "SELECT COUNT(*) FROM {$tableSql} {$whereSql}";
            $total = (int) R::getCell($totalQuery, $bindings);
            $totalPages = $pageSize > 0 ? (int) ceil($total / $pageSize) : 0;

            $query = "SELECT * FROM {$tableSql} {$whereSql} {$orderSql} LIMIT :offset, :pageSize";
            $records = R::getAll($query, array_merge($bindings, [
                ':offset' => $offset,
                ':pageSize' => $pageSize
            ]));

            $result = [
                'data' => $records,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $pageSize,
                'total_pages' => $totalPages,
                'has_more' => $page < $totalPages,
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $pageSize, $total)
            ];

            R::commit();
            $this->cache[$cacheKey] = $result;

            return $result;
        } catch (\Exception $e) {
            R::rollback();
            throw $e;
        }
    }

    /**
     * Clear pagination cache for a specific table
     */
    public function clearPaginationCache(string $table)
    {
        foreach ($this->cache as $key => $value) {
            if (strpos($key, "{$table}_page_") === 0) {
                unset($this->cache[$key]);
            }
        }
    }

    /**
     * Delete a record
     *
     * @return int
     */
    public function delete(int $id, string $table)
    {
        $table = $this->assertIdentifier($table);

        R::begin();
        try {
            $bean = R::load($table, $id);
            R::trash($bean);
            R::commit();

            unset($this->cache["{$table}_{$id}"]);
            unset($this->cache["all_{$table}"]);

            return R::getQueryCount();
        } catch (\Exception $e) {
            R::rollback();
            throw $e;
        }
    }

    /**
     * Delete multiple records in batch
     *
     * @param list<int> $ids
     * @return int
     */
    public function deleteBatch(array $ids, string $table)
    {
        $table = $this->assertIdentifier($table);
        if ($ids === []) {
            return 0;
        }

        R::begin();
        try {
            foreach ($ids as $id) {
                $bean = R::load($table, (int) $id);
                R::trash($bean);
                unset($this->cache["{$table}_{$id}"]);
            }
            R::commit();
            unset($this->cache["all_{$table}"]);
            return R::getQueryCount();
        } catch (\Exception $e) {
            R::rollback();
            throw $e;
        }
    }

    /**
     * Update a record
     *
     * @param array<string, mixed> $data
     * @return int
     */
    public function update(int $id, array $data, string $table)
    {
        $data = $this->filterAssignable($data);
        $table = $this->assertIdentifier($table);
        if ($data === []) {
            return 0;
        }

        R::begin();
        try {
            $bean = R::load($table, $id);

            foreach ($data as $key => $value) {
                $bean->$key = $value;
            }

            R::store($bean);
            R::commit();

            unset($this->cache["{$table}_{$id}"]);
            unset($this->cache["all_{$table}"]);

            return R::getQueryCount();
        } catch (\Exception $e) {
            R::rollback();
            throw $e;
        }
    }

    /**
     * Clear all cache
     */
    public function clearCache()
    {
        $this->cache = [];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function filterAssignable(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (!is_string($key) || !$this->isSafeIdentifier($key)) {
                continue;
            }
            if ($this->fillable !== [] && !in_array($key, $this->fillable, true)) {
                continue;
            }
            $out[$key] = $value;
        }
        return $out;
    }

    protected function isSafeIdentifier(string $name): bool
    {
        return (bool) preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name);
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function assertIdentifier(string $name): string
    {
        if (!$this->isSafeIdentifier($name)) {
            throw new \InvalidArgumentException('Invalid SQL identifier: ' . $name);
        }
        return $name;
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function quoteIdentifier(string $name): string
    {
        return '`' . $this->assertIdentifier($name) . '`';
    }
}

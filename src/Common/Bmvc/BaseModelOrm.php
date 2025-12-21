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
 * BaseOrm
 */
class BaseModelOrm
{
    /**
     * @var PDO
     */
    protected $conn;

    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @var PDO
     */
    private static $connection = null;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (self::$connection === null) {
            self::$connection = (new Database())->getConnection();
            $this->conn = self::$connection;
            $this->readBeanPhp();
           // R::fancyDebug(TRUE);
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
     * @param array $data
     * @param string $table
     * @return int
     */
    public function create(array $data, string $table)
    {
        if (empty($data)) {
            return 0;
        }

        R::begin();
        try {
            $bean = R::dispense($table);
            $sanitizedData = array_map([$this, 'sanitize'], $data);

            foreach ($sanitizedData as $key => $value) {
                $bean->$key = $value;
            }

            $id = R::store($bean);
            R::commit();
            return $id;
        } catch (\Exception $e) {
            R::rollback();
            throw $e;
        }
    }

    /**
     * Create multiple records in batch
     * 
     * @param array $dataSet
     * @param string $table
     * @return array
     */
    public function createBatch(array $dataSet, string $table)
    {
        if (empty($dataSet)) {
            return [];
        }

        $ids = [];
        R::begin();
        try {
            foreach ($dataSet as $data) {
                $bean = R::dispense($table);
                $sanitizedData = array_map([$this, 'sanitize'], $data);
                foreach ($sanitizedData as $key => $value) {
                    $bean->$key = $value;
                }
                $ids[] = R::store($bean);
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
     * @param string $table
     * @return array
     */
    public function readAll(string $table)
    {
        $cacheKey = "all_{$table}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $result = R::getAll("SELECT * FROM $table");
        $this->cache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Read a single record
     * 
     * @param int $id
     * @param string $table
     * @return array
     */
    public function read(int $id, string $table)
    {
        $cacheKey = "{$table}_{$id}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $sanitizedId = $this->sanitize($id);
        $result = R::getAll(
            "SELECT * FROM $table WHERE id = :id",
            [':id' => $sanitizedId]
        );
        
        $this->cache[$cacheKey] = $result;
        return $result;
    }


     /**
 * Read records with pagination
 * 
 * @param string $table
 * @param int $page Current page number
 * @param int $pageSize Number of items per page
 * @return array Returns ['data' => records, 'total' => total_records, 'pages' => total_pages]
 */
public function readWithPagination(string $table, int $page = 1, int $pageSize = 3)
{
    // Ensure valid pagination parameters
    $page = max(1, $page);
    $pageSize = max(1, $pageSize);
    $offset = ($page - 1) * $pageSize;

    $cacheKey = "{$table}_page_{$page}_size_{$pageSize}";
    
    if (isset($this->cache[$cacheKey])) {
        return $this->cache[$cacheKey];
    }

    R::begin();
    try {
        // Get total count of records
        $total = R::count($table);
        $totalPages = ceil($total / $pageSize);

        // Get paginated data
        $records = R::getAll(
            "SELECT * FROM $table LIMIT :offset, :pageSize",
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
        
        // Cache the results
        $this->cache[$cacheKey] = $result;
        
        return $result;
    } catch (\Exception $e) {
        R::rollback();
        throw $e;
    }
}

/**
 * Read paginated records with additional options
 * 
 * @param string $table
 * @param int $page
 * @param int $pageSize
 * @param array $options Array with 'where', 'orderBy', 'search' keys
 * @return array
 */
public function readWithAdvancedPagination(string $table, int $page = 1, int $pageSize = 10, array $options = [])
{
    $page = max(1, $page);
    $pageSize = max(1, $pageSize);
    $offset = ($page - 1) * $pageSize;

    // Build query parts
    $where = isset($options['where']) ? "WHERE {$options['where']}" : '';
    $orderBy = isset($options['orderBy']) ? "ORDER BY {$options['orderBy']}" : '';
    
    $cacheKey = "{$table}_page_{$page}_size_{$pageSize}_" . md5(json_encode($options));
    
    if (isset($this->cache[$cacheKey])) {
        return $this->cache[$cacheKey];
    }

    R::begin();
    try {
        // Get total count with filters
        $totalQuery = "SELECT COUNT(*) FROM $table $where";
        $total = (int) R::getCell($totalQuery);
        $totalPages = $pageSize > 0 ? (int) ceil($total / $pageSize) : 0;

        // Get paginated data with filters and ordering
        $query = "SELECT * FROM $table $where $orderBy LIMIT :offset, :pageSize";
        $records = R::getAll($query, [
            ':offset' => $offset,
            ':pageSize' => $pageSize
        ]);

        $result = [
            'data' => $records,
            'total' => $total,
            'current_page' => $page,
            'per_page' => $pageSize,
            'total_pages' => $totalPages,
            'has_more' => $page < $totalPages,
            'from' => $offset + 1,
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
 * 
 * @param string $table
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
     * @param int $id
     * @param string $table
     * @return int
     */
    public function delete(int $id, string $table)
    {
        $sanitizedId = $this->sanitize($id);
        
        R::begin();
        try {
            $bean = R::load($table, $sanitizedId);
            R::trash($bean);
            R::commit();

            // Clear cache for this entry
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
     * @param array $ids
     * @param string $table
     * @return int
     */
    public function deleteBatch(array $ids, string $table)
    {
        if (empty($ids)) {
            return 0;
        }

        R::begin();
        try {
            foreach ($ids as $id) {
                $sanitizedId = $this->sanitize($id);
                $bean = R::load($table, $sanitizedId);
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
     * @param int $id
     * @param array $data
     * @param string $table
     * @return int
     */
    public function update(int $id, array $data, string $table)
    {
        if (empty($data)) {
            return 0;
        }

        $sanitizedId = $this->sanitize($id);
        
        R::begin();
        try {
            $bean = R::load($table, $sanitizedId);
            $sanitizedData = array_map([$this, 'sanitize'], $data);
            
            foreach ($sanitizedData as $key => $value) {
                $bean->$key = $value;
            }
            
            R::store($bean);
            R::commit();
            
            // Clear cache for this entry
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
     * Sanitizes the input data to prevent SQL injection and XSS attacks.
     *
     * @param mixed $input
     * @return mixed
     */
    private function sanitize($input)
    {
        if (is_string($input)) {
            return htmlspecialchars(
                strip_tags($input),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            );
        } elseif (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        return $input;
    }
}






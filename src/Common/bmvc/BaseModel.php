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

/**
 * BaseModel
 */
class BaseModel
{
    /**
     * @var PDO
     */
    protected $conn;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
    }

    /**
     * Creates a new record in the database.
     *
     * @param array $data The data to be inserted.
     * @param string $table The table name.
     * @return int|false The last inserted ID or false on failure.
     */
    public function create(array $data, string $table)
    {
        if (empty($data)) {
            return false;
        }
    
        $sanitizedData = array_map([$this, 'sanitize'], $data);
        $placeholders = implode(', ', array_fill(0, count($sanitizedData), '?'));
        $columns = implode(', ', array_keys($sanitizedData));
    
        $stmt = $this->conn->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
        if (!$stmt) {
            return false;
        }
    
        // Bind parameter with value
        $i = 1;
        foreach ($sanitizedData as $value) {
            $stmt->bindValue($i++, $value);
        }
    
        $success = $stmt->execute();
        if ($success) {
            return $this->conn->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * Reads a record from the database.
     *
     * @param int $id The record ID.
     * @param string $table The table name.
     * @return array|null The record data or null on failure.
     */
    public function read(int $id, string $table)
    {
        $sanitizedId = $this->sanitize($id);
        $stmt = $this->conn->prepare("SELECT * FROM $table WHERE id = :id");
        if (!$stmt) {
            return null;
        }

        $stmt->bindParam(':id', $sanitizedId);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Reads all records from the database.
     *
     * @param string $table The table name.
     * @return array The records data.
     */
    public function readAll(string $table)
    {
        $stmt = $this->conn->prepare("SELECT * FROM $table");
        if (!$stmt) {
            return [];
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reads records from the database with pagination.
     *
     * @param string $table The table name.
     * @param int $page The current page.
     * @param int $pageSize The number of records per page.
     * @return array The records data.
     */
    public function readWithPagination(string $table, int $page, int $pageSize)
    {
        $offset = ($page - 1) * $pageSize;
        $stmt = $this->conn->prepare("SELECT * FROM $table LIMIT :offset, :pageSize");
        if (!$stmt) {
            return [];
        }

        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':pageSize', $pageSize, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Updates a record in the database.
     *
     * @param int $id The record ID.
     * @param array $data The data to be updated.
     * @param string $table The table name.
     * @return bool True on success, false on failure.
     */
    public function update(int $id, array $data, string $table)
    {
        if (empty($data)) {
            return false;
        }

        $sanitizedId = $this->sanitize($id);
        $sanitizedData = array_map([$this, 'sanitize'], $data);
        $setClause = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($sanitizedData)));

        $stmt = $this->conn->prepare("UPDATE $table SET $setClause WHERE id = :id");
        if (!$stmt) {
            return false;
        }

        $stmt->bindParam(':id', $sanitizedId);
        foreach ($sanitizedData as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        $success = $stmt->execute();
        $rowCount = $stmt->rowCount();
        return $rowCount > 0 ? $success : false;
    }

    /**
     * Deletes a record from the database.
     *
     * @param int $id The record ID.
     * @param string $table The table name.
     * @return bool True on success, false on failure.
     */
    public function delete(int $id, string $table)
    {
        $sanitizedId = $this->sanitize($id);
        $stmt = $this->conn->prepare("DELETE FROM $table WHERE id = :id");
        if (!$stmt) {
            return false;
        }

        $stmt->bindParam(':id', $sanitizedId);
        $success = $stmt->execute();
        $rowCount = $stmt->rowCount();
        return $rowCount > 0 ? $success : false;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->conn = null;
    }

    /**
     * Sanitizes the input data to prevent SQL injection and XSS attacks.
     *
     * @param mixed $input The input data to be sanitized.
     * @return string The sanitized input.
     */
    private function sanitize($input)
    {
        if (is_string($input)) {
            // Sanitize string input
            $sanitizedInput = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            if ($sanitizedInput === false) {
                return "Error during sanitization.";
            }
    
            // Escape special characters to prevent XSS
            return htmlspecialchars($sanitizedInput, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } elseif (is_array($input)) {
            // Recursively sanitize array elements
            return array_map([$this, 'sanitize'], $input);
        } else {
            // Return non-string input as is
            return $input;
        }
    }
}






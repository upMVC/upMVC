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
 * BaseModel — thin PDO CRUD helpers.
 *
 * Identifiers (table / column names) must match {@see isSafeIdentifier()}.
 * Set {@see $fillable} on subclasses to restrict mass-assignment keys;
 * empty $fillable keeps BC (any safe identifier key is accepted).
 * Values are bound via PDO — do not HTML-escape here (escape in views).
 */
class BaseModel
{
    /**
     * @var PDO|null
     */
    protected $conn;

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
        $this->conn = (new Database())->getConnection();

        if ($this->conn === null) {
            throw new \RuntimeException(
                'Database connection could not be established. Please check DB_* settings in .env or ConfigDatabase. '
            );
        }
    }

    /**
     * Creates a new record in the database.
     *
     * @param array<string, mixed> $data
     * @return int|false|string The last inserted ID or false on failure.
     */
    public function create(array $data, string $table = '')
    {
        $data = $this->filterAssignable($data);
        if ($data === [] || $table === '') {
            return false;
        }

        $tableSql = $this->quoteIdentifier($table);
        $columns = [];
        $placeholders = [];
        foreach (array_keys($data) as $key) {
            $columns[] = $this->quoteIdentifier($key);
            $placeholders[] = '?';
        }

        $stmt = $this->conn->prepare(
            'INSERT INTO ' . $tableSql . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')'
        );
        if (!$stmt) {
            return false;
        }

        $i = 1;
        foreach ($data as $value) {
            $stmt->bindValue($i++, $value);
        }

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Reads a record from the database.
     *
     * @return array|false|null
     */
    public function read(int $id, string $table = '')
    {
        if ($table === '') {
            return null;
        }

        $stmt = $this->conn->prepare(
            'SELECT * FROM ' . $this->quoteIdentifier($table) . ' WHERE `id` = :id'
        );
        if (!$stmt) {
            return null;
        }

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Reads all records from the database.
     *
     * @return list<array<string, mixed>>
     */
    public function readAll(string $table = '')
    {
        if ($table === '') {
            return [];
        }

        $stmt = $this->conn->prepare('SELECT * FROM ' . $this->quoteIdentifier($table));
        if (!$stmt) {
            return [];
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Reads records from the database with pagination.
     *
     * @return list<array<string, mixed>>
     */
    public function readWithPagination(string $table = '', int $page = 1, int $pageSize = 10)
    {
        if ($table === '') {
            return [];
        }

        $page = max(1, $page);
        $pageSize = max(1, min(self::MAX_PAGE_SIZE, $pageSize));
        $offset = ($page - 1) * $pageSize;

        $stmt = $this->conn->prepare(
            'SELECT * FROM ' . $this->quoteIdentifier($table) . ' LIMIT :offset, :pageSize'
        );
        if (!$stmt) {
            return [];
        }

        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':pageSize', $pageSize, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Updates a record in the database.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data, string $table = ''): bool
    {
        $data = $this->filterAssignable($data);
        if ($data === [] || $table === '') {
            return false;
        }

        $setParts = [];
        foreach (array_keys($data) as $key) {
            $setParts[] = $this->quoteIdentifier($key) . ' = :' . $key;
        }

        $stmt = $this->conn->prepare(
            'UPDATE ' . $this->quoteIdentifier($table)
            . ' SET ' . implode(', ', $setParts)
            . ' WHERE `id` = :id'
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->rowCount() > 0;
    }

    /**
     * Deletes a record from the database.
     */
    public function delete(int $id, string $table = ''): bool
    {
        if ($table === '') {
            return false;
        }

        $stmt = $this->conn->prepare(
            'DELETE FROM ' . $this->quoteIdentifier($table) . ' WHERE `id` = :id'
        );
        if (!$stmt) {
            return false;
        }

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return false;
        }

        return $stmt->rowCount() > 0;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->conn = null;
    }

    /**
     * Keep only assignable keys with safe SQL identifier names.
     *
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
     * Quote a validated SQL identifier with backticks.
     *
     * @throws \InvalidArgumentException
     */
    protected function quoteIdentifier(string $name): string
    {
        if (!$this->isSafeIdentifier($name)) {
            throw new \InvalidArgumentException('Invalid SQL identifier: ' . $name);
        }
        return '`' . $name . '`';
    }
}

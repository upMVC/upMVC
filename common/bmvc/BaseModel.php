<?php
/*
 *   Created on Tue Oct 31 2023
 
 *   Copyright (c) 2023 BitsHost
 *   All rights reserved.

 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:

 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.

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

namespace Common\Bmvc;

use upMVC\Database;
use PDO;


class BaseModel
{
    protected $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn       = $database->getConnection();
    }

    public function create($data, $table)
    {
        // Check if data is provided and is an array
        if (empty($data) || !is_array($data)) {
            return false;
        }

        // Sanitize the data
        $sanitizedData = array_map([$this, 'sanitize'], $data);

        // Generate placeholders for the values
        $placeholders = implode(', ', array_fill(0, count($sanitizedData), '?'));

        // Generate column names for the SQL query
        $columns = implode(', ', array_keys($sanitizedData));

        // Generate the prepared statement
        $stmt = $this->conn->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");

        if (!$stmt) {
            // Handle the error, e.g., log it or return false
            return false;
        }

        // Bind parameters with values
        $stmt->execute(array_values($sanitizedData));

        // Return the last inserted ID if needed
        return $this->conn->lastInsertId();
    }

    public function read($id, $table)
    {
        // Sanitize the input
        $sanitizedId = $this->sanitize($id);

        // Generate the prepared statement
        $stmt = $this->conn->prepare("SELECT * FROM $table WHERE id = :id");

        if (!$stmt) {
            // Handle the error, e.g., log it or return null
            return null;
        }

        // Bind parameter with value
        $stmt->bindParam(':id', $sanitizedId);

        // Execute the statement
        $stmt->execute();

        // Fetch the record as an associative array
        $record = $stmt->fetch();

        return $record;
    }

    public function readAll($table)
    {
        // Generate the prepared statement
        $stmt = $this->conn->prepare("SELECT * FROM $table");

        if (!$stmt) {
            // Handle the error, e.g., log it or return an empty array
            return [];
        }

        // Execute the statement
        $stmt->execute();

        // Fetch all records as an associative array
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $records;
    }

    public function readWithPagination($table, $page, $pageSize)
    {
        // Calculate the offset based on the page and pageSize
        $offset = ($page - 1) * $pageSize;

        // Generate the prepared statement for paginated results
        $stmt = $this->conn->prepare("SELECT * FROM $table LIMIT :offset, :pageSize");

        if (!$stmt) {
            // Handle the error, e.g., log it or return an empty array
            return [];
        }

        // Bind parameters with values
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':pageSize', $pageSize, PDO::PARAM_INT);

        // Execute the statement
        $stmt->execute();

        // Fetch all records as an associative array
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $records;
    }


    public function update($id, $data, $table)
    {
        // Check if data is provided and is an array
        if (empty($data) || !is_array($data)) {
            return false;
        }

        // Sanitize the input
        $sanitizedId = $this->sanitize($id);

        // Sanitize the data
        $sanitizedData = array_map([$this, 'sanitize'], $data);

        // Generate the SET part of the SQL query
        $setClause = implode(', ', array_map(function ($key) {
            return "$key = :$key";
        }, array_keys($sanitizedData)));

        // Generate the prepared statement
        $stmt = $this->conn->prepare("UPDATE $table SET $setClause WHERE id = :id");

        if (!$stmt) {
            // Handle the error, e.g., log it or return false
            return false;
        }

        // Bind parameters with values
        $stmt->bindParam(':id', $sanitizedId);
        foreach ($sanitizedData as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        // Execute the statement
        $success = $stmt->execute();
        // If the UPDATE statement doesn't change any rows (for example, if the WHERE condition doesn't
        // match any existing rows), $rowCount will be 0.
        $rowCount = $stmt->rowCount();

        if ($rowCount > 0) {
            return $success;
        } else {
            return false;
        }
    }

    public function delete($id, $table)
    {
        // Sanitize the input
        $sanitizedId = $this->sanitize($id);

        // Generate the prepared statement
        $stmt = $this->conn->prepare("DELETE FROM $table WHERE id = :id");

        if (!$stmt) {
            // Handle the error, e.g., log it or return false
            return false;
        }

        // Bind parameter with value
        $stmt->bindParam(':id', $sanitizedId);

        // Execute the statement
        $success = $stmt->execute();
        // If the UPDATE statement doesn't change any rows (for example, if the WHERE condition doesn't
        // match any existing rows), $rowCount will be 0.
        $rowCount = $stmt->rowCount();

        if ($rowCount > 0) {
            return $success;
        } else {
            return false;
        }
    }

    public function __destruct()
    {
        // Close the database connection when the object is destroyed
        $this->conn = null;
    }

    // Sanitize method to prevent SQL injection
    private function sanitize($input)
    {
        // Additional filters can be added here based on your requirements
        $sanitizedInput = filter_var($input, FILTER_SANITIZE_STRING);

        if ($sanitizedInput === false) {
            // Handle the error, log it, or return an error message
            return "Error during sanitization.";
        }

        $sanitizedInput = filter_var($sanitizedInput, FILTER_SANITIZE_SPECIAL_CHARS);

        if ($sanitizedInput === false) {
            // Handle the error, log it, or return an error message
            return "Error during sanitization.";
        }

        // Example of using htmlspecialchars in addition to the above filters
        $sanitizedInput = htmlspecialchars($sanitizedInput, ENT_QUOTES, 'UTF-8');

        return $sanitizedInput;
    }
}

<?php

/*
 *   Created on October 17, 2025
 *   
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 */

namespace App\Modules\Reacthmr;

/**
 * ReactHMR Model
 * 
 * Provides sample data for demo purposes
 */
class Model
{
    /**
     * Get sample users for demo
     * 
     * @return array Sample user data
     */
    public function getSampleUsers(): array
    {
        return [
            ['id' => 1, 'name' => 'Alice Johnson', 'email' => 'alice@example.com', 'role' => 'Admin'],
            ['id' => 2, 'name' => 'Bob Smith', 'email' => 'bob@example.com', 'role' => 'Developer'],
            ['id' => 3, 'name' => 'Charlie Brown', 'email' => 'charlie@example.com', 'role' => 'Designer'],
            ['id' => 4, 'name' => 'Diana Prince', 'email' => 'diana@example.com', 'role' => 'Manager'],
            ['id' => 5, 'name' => 'Eve Wilson', 'email' => 'eve@example.com', 'role' => 'Developer'],
        ];
    }

    /**
     * Get statistics for demo
     * 
     * @return array Sample statistics
     */
    public function getStats(): array
    {
        return [
            'totalUsers' => 5,
            'activeProjects' => 12,
            'completedTasks' => 87,
            'pendingTasks' => 23
        ];
    }

    /**
     * Get sample todos for demo
     * 
     * @return array Sample todo items
     */
    public function getTodos(): array
    {
        return [
            ['id' => 1, 'text' => 'Learn Preact with HMR', 'completed' => true],
            ['id' => 2, 'text' => 'Build a todo app', 'completed' => false],
            ['id' => 3, 'text' => 'Try ES modules', 'completed' => false],
        ];
    }
}












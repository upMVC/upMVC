<?php
/**
 * index.php - Application Entry Point
 * 
 * This is the main entry point for the upMVC framework.
 * All HTTP requests are routed through this file via .htaccess.
 * 
 * Bootstrap Sequence:
 * 1. Load Composer autoloader
 * 2. Instantiate Start class (triggers configuration and initialization)
 * 3. Execute upMVC() method (starts routing and middleware)
 * 
 * Requirements:
 * - PHP 8.0 or higher
 * - Composer dependencies installed (vendor/autoload.php)
 * - .env file configured in /etc directory
 * - .htaccess configured to route all requests to this file
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 * @created Tue Oct 31 2023
 */

// ========================================
// Load Dependencies
// ========================================

require_once 'vendor/autoload.php';

use upMVC\Start;

// ========================================
// Start Application
// ========================================

/**
 * Initialize and start the upMVC application
 * 
 * The Start constructor handles:
 * - Configuration loading from .env
 * - Error handler registration
 * - Request initialization
 * 
 * The upMVC() method handles:
 * - Router setup
 * - Middleware registration
 * - Route dispatching
 */
$fireUpMVC = new Start();
$fireUpMVC->upMVC();

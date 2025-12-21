<?php
/**
 * FormHelper.php - Form and CSRF Helpers
 * 
 * Handles form repopulation and CSRF protection.
 * 
 * @package upMVC\Helpers
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 */

namespace App\Etc\Helpers;

class FormHelper
{
    /**
     * Get old input value from session (for form repopulation)
     */
    public static function old(string $key, $default = '')
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
    
    /**
     * Get CSRF token from session
     */
    public static function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Generate CSRF hidden input field
     */
    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::csrfToken() . '">';
    }
}






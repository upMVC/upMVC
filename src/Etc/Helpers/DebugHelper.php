<?php
/**
 * DebugHelper.php - Debugging Utilities
 * 
 * Provides debugging and development helpers.
 * 
 * @package upMVC\Helpers
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 */

namespace App\Etc\Helpers;

class DebugHelper
{
    /**
     * Dump and die - Debug helper
     */
    public static function dd(...$vars): void
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die(1);
    }
    
    /**
     * Dump variable without dying
     */
    public static function dump(...$vars): void
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
    }
}






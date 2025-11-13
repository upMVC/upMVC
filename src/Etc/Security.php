<?php
/**
 * Security.php - Security Utilities and Protection
 * 
 * This class provides security features for upMVC:
 * - CSRF token generation and validation
 * - Rate limiting by identifier (IP, user, etc.)
 * - Input sanitization (XSS protection)
 * - Input validation with rules
 * 
 * Features:
 * - Timing-safe CSRF validation using hash_equals()
 * - In-memory rate limiting (suitable for single-server setups)
 * - Recursive array sanitization
 * - Rule-based validation (email, URL, int, length constraints)
 * 
 * Usage:
 * - CSRF: Security::csrfToken() in forms, Security::validateCsrf() on submit
 * - Rate Limit: Security::rateLimit($ip, 100, 3600) // 100 requests per hour
 * - Sanitize: $clean = Security::sanitizeInput($_POST)
 * - Validate: $errors = Security::validateInput($data, $rules)
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 */

namespace App\Etc;

class Security
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * In-memory rate limit tracking
     * 
     * Format: ['hash' => ['count' => int, 'reset' => timestamp]]
     * 
     * NOTE: This is in-memory only and resets on server restart.
     * For multi-server environments, consider using Redis or database.
     * 
     * @var array
     */
    private static $rateLimits = [];
    
    // ========================================
    // CSRF Protection
    // ========================================
    
    /**
     * Generate or retrieve CSRF token
     * 
     * Creates a cryptographically secure CSRF token and stores it in session.
     * Returns existing token if already generated in current session.
     * 
     * @return string 64-character hexadecimal CSRF token
     * 
     * @example
     * // In form template
     * <input type="hidden" name="csrf_token" value="<?php echo Security::csrfToken(); ?>">
     */
    public static function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token using timing-safe comparison
     * 
     * Validates submitted token against session token using hash_equals()
     * to prevent timing attacks.
     * 
     * @param string $token CSRF token from form submission
     * @return bool True if token is valid, false otherwise
     * 
     * @example
     * // In controller
     * if (!Security::validateCsrf($_POST['csrf_token'])) {
     *     die('CSRF validation failed');
     * }
     */
    public static function validateCsrf(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    // ========================================
    // Rate Limiting
    // ========================================
    
    /**
     * Check rate limit for identifier
     * 
     * Tracks requests per identifier (IP address, user ID, etc.) within
     * a time window. Automatically resets counter after time window expires.
     * 
     * NOTE: In-memory storage. For production multi-server setups,
     * implement Redis or database-backed rate limiting.
     * 
     * @param string $identifier Unique identifier (IP, user ID, etc.)
     * @param int $maxRequests Maximum requests allowed (default: 100)
     * @param int $timeWindow Time window in seconds (default: 3600 = 1 hour)
     * @return bool True if under limit, false if exceeded
     * 
     * @example
     * // Limit by IP address
     * if (!Security::rateLimit($_SERVER['REMOTE_ADDR'], 100, 3600)) {
     *     http_response_code(429);
     *     die('Rate limit exceeded');
     * }
     * 
     * @example
     * // Limit by user ID with custom limits
     * if (!Security::rateLimit('user_' . $userId, 50, 600)) {
     *     die('Too many requests');
     * }
     */
    public static function rateLimit(string $identifier, int $maxRequests = 100, int $timeWindow = 3600): bool
    {
        $now = time();
        $key = md5($identifier);
        
        // Initialize tracking for new identifier
        if (!isset(self::$rateLimits[$key])) {
            self::$rateLimits[$key] = ['count' => 0, 'reset' => $now + $timeWindow];
        }
        
        $limit = &self::$rateLimits[$key];
        
        // Reset counter if time window expired
        if ($now > $limit['reset']) {
            $limit['count'] = 0;
            $limit['reset'] = $now + $timeWindow;
        }
        
        $limit['count']++;
        
        return $limit['count'] <= $maxRequests;
    }
    
    // ========================================
    // Input Sanitization
    // ========================================
    
    /**
     * Sanitize input to prevent XSS attacks
     * 
     * Recursively sanitizes strings and arrays using htmlspecialchars().
     * Trims whitespace and converts special characters to HTML entities.
     * 
     * Safe for displaying user input in HTML context.
     * 
     * @param mixed $input Input to sanitize (string, array, or other)
     * @return mixed Sanitized input (same type as input)
     * 
     * @example
     * // Sanitize POST data
     * $clean = Security::sanitizeInput($_POST);
     * 
     * @example
     * // Sanitize single string
     * $cleanName = Security::sanitizeInput($_POST['name']);
     * 
     * @example
     * // Sanitize nested array
     * $cleanData = Security::sanitizeInput([
     *     'user' => ['name' => '<script>alert(1)</script>']
     * ]);
     * // Result: ['user' => ['name' => '&lt;script&gt;alert(1)&lt;/script&gt;']]
     */
    public static function sanitizeInput($input): mixed
    {
        // Recursively sanitize arrays
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        // Sanitize strings
        if (is_string($input)) {
            return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        // Return other types unchanged
        return $input;
    }
    
    // ========================================
    // Input Validation
    // ========================================
    
    /**
     * Validate input data against rules
     * 
     * Validates data array against validation rules and returns array of errors.
     * Returns empty array if all validations pass.
     * 
     * Supported rule types:
     * - required: Field must not be empty
     * - type: 'email', 'url', 'int'
     * - min_length: Minimum string length
     * - max_length: Maximum string length
     * 
     * @param array $data Data to validate (e.g., $_POST)
     * @param array $rules Validation rules per field
     * @return array Associative array of errors ['field' => 'error message']
     * 
     * @example
     * // Basic validation
     * $rules = [
     *     'email' => ['required' => true, 'type' => 'email'],
     *     'name' => ['required' => true, 'min_length' => 3, 'max_length' => 50],
     *     'age' => ['type' => 'int']
     * ];
     * 
     * $errors = Security::validateInput($_POST, $rules);
     * if (!empty($errors)) {
     *     // Handle validation errors
     *     foreach ($errors as $field => $error) {
     *         echo "$field: $error<br>";
     *     }
     * }
     * 
     * @example
     * // With custom error handling
     * $errors = Security::validateInput($data, [
     *     'username' => ['required' => true, 'min_length' => 5],
     *     'website' => ['type' => 'url']
     * ]);
     */
    public static function validateInput(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Check required fields
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = "Field {$field} is required";
                continue;
            }
            
            // Validate non-empty values
            if (!empty($value)) {
                // Type validation
                if (isset($rule['type'])) {
                    switch ($rule['type']) {
                        case 'email':
                            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                                $errors[$field] = "Invalid email format";
                            }
                            break;
                        case 'url':
                            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                                $errors[$field] = "Invalid URL format";
                            }
                            break;
                        case 'int':
                            if (!filter_var($value, FILTER_VALIDATE_INT)) {
                                $errors[$field] = "Must be an integer";
                            }
                            break;
                    }
                }
                
                // Length validation
                if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                    $errors[$field] = "Minimum length is {$rule['min_length']}";
                }
                
                if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                    $errors[$field] = "Maximum length is {$rule['max_length']}";
                }
            }
        }
        
        return $errors;
    }
}






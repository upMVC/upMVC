<?php

namespace upMVC;

class Security
{
    private static $rateLimits = [];
    
    public static function csrfToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateCsrf(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function rateLimit(string $identifier, int $maxRequests = 100, int $timeWindow = 3600): bool
    {
        $now = time();
        $key = md5($identifier);
        
        if (!isset(self::$rateLimits[$key])) {
            self::$rateLimits[$key] = ['count' => 0, 'reset' => $now + $timeWindow];
        }
        
        $limit = &self::$rateLimits[$key];
        
        if ($now > $limit['reset']) {
            $limit['count'] = 0;
            $limit['reset'] = $now + $timeWindow;
        }
        
        $limit['count']++;
        
        return $limit['count'] <= $maxRequests;
    }
    
    public static function sanitizeInput($input): mixed
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        
        if (is_string($input)) {
            return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        return $input;
    }
    
    public static function validateInput(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            if (isset($rule['required']) && $rule['required'] && empty($value)) {
                $errors[$field] = "Field {$field} is required";
                continue;
            }
            
            if (!empty($value)) {
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
<?php
/**
 * LIVE PATTERN TESTER
 * Run this file to test any pattern
 */

// Simulate the Router's convertToRegex method
function convertToRegex($route)
{
    // Escape forward slashes
    $pattern = str_replace('/', '\/', $route);
    
    // Replace * with regex pattern
    $pattern = str_replace('*', '([^\/]+)', $pattern);
    
    // Replace {param} with named capture group
    $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^\/]+)', $pattern);
    
    // Add start and end anchors
    return '/^' . $pattern . '$/';
}

// Test function
function testPattern($pattern, $testRoutes)
{
    echo "═══════════════════════════════════════════════════════════\n";
    echo "PATTERN: {$pattern}\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    
    $regex = convertToRegex($pattern);
    echo "REGEX: {$regex}\n\n";
    
    foreach ($testRoutes as $route) {
        echo "Testing: {$route}\n";
        
        if (preg_match($regex, $route, $matches)) {
            echo "  ✅ MATCH!\n";
            echo "  Parameters extracted:\n";
            foreach ($matches as $key => $value) {
                if (!is_numeric($key)) {
                    echo "    - \$_GET['{$key}'] = '{$value}'\n";
                }
            }
        } else {
            echo "  ❌ NO MATCH\n";
        }
        echo "\n";
    }
    echo "\n";
}

// ═══════════════════════════════════════════════════════════
// TEST 1: Your question - books/autor/year/{id}
// ═══════════════════════════════════════════════════════════

testPattern('/books/autor/year/{id}', [
    '/books/autor/year/123',                    // Should match
    '/books/autor/year/the-great-gatsby',       // Should match
    '/books/autor/year/978-0-123456-78-9',      // Should match (ISBN)
    '/books/autor/year/',                       // Should NOT match (no ID)
    '/books/autor/year/123/extra',              // Should NOT match (extra segment)
    '/books/other/year/123',                    // Should NOT match (wrong path)
]);

// ═══════════════════════════════════════════════════════════
// TEST 2: Multiple parameters
// ═══════════════════════════════════════════════════════════

testPattern('/books/{author}/{year}/{id}', [
    '/books/tolkien/1954/lord-of-the-rings',    // Should match
    '/books/orwell/1949/1984',                  // Should match
    '/books/author-name/2024/book-slug',        // Should match
    '/books/tolkien/1954/',                     // Should NOT match (missing ID)
    '/books/tolkien/1954',                      // Should NOT match (missing segment)
]);

// ═══════════════════════════════════════════════════════════
// TEST 3: Wildcard pattern
// ═══════════════════════════════════════════════════════════

testPattern('/admin/users/edit/*', [
    '/admin/users/edit/1',                      // Should match
    '/admin/users/edit/999',                    // Should match
    '/admin/users/edit/abc-123',                // Should match
    '/admin/users/edit/',                       // Should NOT match
    '/admin/users/edit/1/extra',                // Should NOT match
]);

// ═══════════════════════════════════════════════════════════
// TEST 4: Mixed patterns
// ═══════════════════════════════════════════════════════════

testPattern('/shop/{category}/product/*', [
    '/shop/electronics/product/laptop-123',     // Should match
    '/shop/books/product/978-0-123',            // Should match
    '/shop/category/product/123',               // Should match
    '/shop/electronics/product/',               // Should NOT match
]);

// ═══════════════════════════════════════════════════════════
// TEST 5: Complex real-world pattern
// ═══════════════════════════════════════════════════════════

testPattern('/api/v1/users/{userId}/posts/{postId}/comments/*', [
    '/api/v1/users/123/posts/456/comments/789',           // Should match
    '/api/v1/users/john-doe/posts/my-post/comments/1',    // Should match
    '/api/v1/users/123/posts/456/comments/',              // Should NOT match
]);

echo "═══════════════════════════════════════════════════════════\n";
echo "ALL TESTS COMPLETE\n";
echo "═══════════════════════════════════════════════════════════\n\n";

echo "💡 HOW TO RUN THIS TEST:\n";
echo "php modules/admin/PATTERN_TESTER.php\n\n";

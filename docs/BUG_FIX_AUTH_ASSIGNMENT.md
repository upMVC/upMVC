# Critical Bug Fix: Assignment vs Comparison in Auth Controller

## Problem Found
Date: October 16, 2025 - 18:22

### Symptom
After fixing the AuthMiddleware, the intended_url was being stored correctly, but users were still redirected to home `/` instead of the intended URL after login.

### Log Evidence
```log
[2025-10-16 18:22:28] Storing intended_url: /upMVC/moda ✅ CORRECT
[2025-10-16 18:22:34] POST /auth → Login successful
[2025-10-16 18:22:34] Redirected to / ❌ WRONG (should be /moda)
```

## Root Cause

### Bug #1: Assignment instead of Comparison (Line 68)

**File:** `d:\GitHub\upMVC\modules\auth\Controller.php`

```php
// WRONG (Bug)
if (isset($_SESSION["logged"]) && $_SESSION["logged"] = true) {
//                                                     ^ ASSIGNMENT!
```

This uses `=` (assignment) instead of `===` (comparison), which means:
1. It **ALWAYS assigns** `$_SESSION["logged"] = true`
2. The assignment **ALWAYS returns true**
3. So this condition **ALWAYS evaluates to true**
4. User is redirected to home even when not logged in!

### Bug #2: auth() method doesn't check intended_url

When a logged-in user visits `/auth`, the method should check for `intended_url` and redirect there, not just to home.

## The Fix

```php
// CORRECT (Fixed)
private function auth()
{
    // Fix: Use comparison (===) not assignment (=)
    if (isset($_SESSION["logged"]) && $_SESSION["logged"] === true) {
        // If already logged in, redirect to intended URL or home
        $intendedUrl = $_SESSION['intended_url'] ?? null;
        if ($intendedUrl) {
            unset($_SESSION['intended_url']); // Clear intended URL
            header("Location: $intendedUrl");
        } else {
            $this->url = BASE_URL;
            header("Location: $this->url");
        }
    } else {
        $this->login();
    }
}
```

## Complete Flow After All Fixes

### Scenario: Visit Protected Route While Logged Out

```
1. User visits /moda (not logged in)
   ↓
2. AuthMiddleware:
   - $_SESSION['intended_url'] = '/upMVC/moda'
   - Redirect to /auth
   ↓
3. GET /auth → display() → auth()
   - Check: logged === true? NO
   - Call login() method
   - Show login form
   ↓
4. User submits login form
   ↓
5. POST /auth → display() → auth()
   - Check: logged === true? NO (not yet!)
   - Call login() method
   - Validate credentials
   - Set $_SESSION['logged'] = true
   - Check intended_url: '/upMVC/moda' ✅
   - Redirect to '/upMVC/moda' ✅
```

### Scenario: Visit Auth When Already Logged In

```
1. User visits /auth (already logged in)
   ↓
2. GET /auth → display() → auth()
   - Check: logged === true? YES
   - Check intended_url: exists? YES
   - Redirect to intended_url
   OR
   - Check intended_url: exists? NO
   - Redirect to home
```

## Testing

### Test Case 1: Protected Route → Login → Redirect Back
```powershell
# 1. Logout first
# 2. Visit http://localhost/upMVC/moda
# 3. Should redirect to /upMVC/auth
# 4. Login with valid credentials
# Expected: Redirect to /upMVC/moda ✅
```

### Test Case 2: Direct Login
```powershell
# 1. Logout first
# 2. Visit http://localhost/upMVC/auth directly
# 3. Login with valid credentials
# Expected: Redirect to /upMVC/ (home) ✅
```

### Test Case 3: Visit Auth When Logged In
```powershell
# 1. Login first
# 2. Visit http://localhost/upMVC/auth
# Expected: Redirect to /upMVC/ (home) ✅
```

## Debug Output

After the fix, you should see in the log:

```log
[timestamp] DEBUG AuthMiddleware - Storing intended_url: /upMVC/moda
[timestamp] DEBUG Auth Controller - Login successful
[timestamp] DEBUG Auth Controller - intended_url in session: /upMVC/moda
[timestamp] DEBUG Auth Controller - Redirecting to intended: /upMVC/moda
[timestamp] DEBUG Router - reqRoute: /moda
[timestamp] DEBUG Router - reqURI: /upMVC/moda
```

## Common PHP Pitfalls

### Assignment vs Comparison
```php
// ❌ WRONG - Assignment (always true)
if ($var = true) { }

// ✅ CORRECT - Comparison
if ($var === true) { }
if ($var == true) { }  // Also works but less strict
```

### Why This Bug is Dangerous
1. **Silent failure** - No error, just wrong behavior
2. **Security risk** - Could bypass authentication checks
3. **Hard to spot** - Single character difference (= vs ==)

## Prevention

### Use Strict Comparison
Always use `===` instead of `==` in PHP:
```php
// Loose comparison (can have unexpected results)
if ($logged == true) { }

// Strict comparison (recommended)
if ($logged === true) { }
```

### Enable Strict Types (PHP 7+)
Add at the top of files:
```php
<?php
declare(strict_types=1);
```

### Use Linters
- PHPStan
- Psalm
- PHP_CodeSniffer

These can catch `=` vs `===` errors automatically.

## Files Changed
1. `d:\GitHub\upMVC\modules\auth\Controller.php`
   - Fixed assignment operator (= → ===)
   - Added intended_url check in auth() method
   - Added debug logging

## Related Documentation
- See: `BUG_FIX_AUTH_REDIRECT.md` for the AuthMiddleware fix

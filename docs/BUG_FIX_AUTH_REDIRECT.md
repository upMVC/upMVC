# Bug Fix: Authentication Redirect Loop

## Problem Discovered
Date: October 16, 2025

### Issue
When visiting a protected route (e.g., `/moda`) while not authenticated:
1. ✅ User visits `/moda` → Middleware stores `$_SESSION['intended_url'] = /upMVC/moda`
2. ❌ Redirect to `/auth` → Middleware runs AGAIN and **OVERWRITES** `$_SESSION['intended_url'] = /upMVC/auth`
3. ❌ After login → Redirects to `/upMVC/auth` instead of `/upMVC/moda`
4. ❌ Since user is now logged in, `/auth` redirects to home `/`
5. ❌ User never reaches the original `/moda` page

### Root Cause
The `AuthMiddleware` was storing `intended_url` on **EVERY** request that required auth, even when redirecting to the login page itself. This caused the original intended URL to be overwritten with `/auth`.

### Debug Log Evidence
```log
[17:58:42] Visit /moda → Storing intended_url: /upMVC/moda ✅ CORRECT
[17:58:42] Redirect to /auth → request[uri]: /upMVC/auth ❌ OVERWRITES!
[18:00:04] GET /auth → request[uri]: /upMVC/auth ❌ STILL OVERWRITING!
```

## Solution

### Fix #1: AuthMiddleware.php
**File:** `d:\GitHub\upMVC\etc\Middleware\AuthMiddleware.php`

**Change:** Only store `intended_url` if it's not already set in session

```php
// BEFORE (Bug)
if (!$this->isAuthenticated()) {
    $intendedUrl = $request['uri'];
    $_SESSION['intended_url'] = $intendedUrl;  // Always overwrites!
    header('Location: ' . $baseUrl . $this->redirectTo);
    exit;
}

// AFTER (Fixed)
if (!$this->isAuthenticated()) {
    // Store intended URL ONLY if not already set
    if (!isset($_SESSION['intended_url'])) {
        $intendedUrl = $request['uri'];
        $_SESSION['intended_url'] = $intendedUrl;
    }
    header('Location: ' . $baseUrl . $this->redirectTo);
    exit;
}
```

### Fix #2: Auth Controller.php
**File:** `d:\GitHub\upMVC\modules\auth\Controller.php`

**Change:** Don't double-prepend BASE_URL (it's already in the URI)

```php
// BEFORE (Bug)
if ($intendedUrl) {
    $redirectUrl = BASE_URL . $intendedUrl;  // Double BASE_URL!
    unset($_SESSION['intended_url']);
}

// AFTER (Fixed)
if ($intendedUrl) {
    // The intended_url already contains BASE_URL path (e.g., /upMVC/moda)
    $redirectUrl = $intendedUrl;
    unset($_SESSION['intended_url']);
}
```

## Testing

### Test Case 1: Protected Route Redirect
1. Logout
2. Visit protected route `/moda`
3. Should redirect to `/auth`
4. Login with valid credentials
5. **Expected:** Redirect to `/moda` ✅
6. **Previous Behavior:** Redirect to `/` ❌

### Test Case 2: Direct Login
1. Logout
2. Visit `/auth` directly
3. Login with valid credentials
4. **Expected:** Redirect to `/` (home) ✅
5. **Behavior:** Should work correctly ✅

### Debug Commands
Watch the debug log in real-time:
```powershell
Get-Content d:\GitHub\upMVC\logs\debug_2025-10-16.log -Wait -Tail 20
```

## Flow Diagrams

### Before Fix (Bug)
```
User → /moda (not logged)
  ↓
AuthMiddleware: $_SESSION['intended_url'] = '/upMVC/moda'
  ↓
Redirect → /auth
  ↓
AuthMiddleware: $_SESSION['intended_url'] = '/upMVC/auth' ❌ OVERWRITE!
  ↓
User logs in
  ↓
Controller reads: $_SESSION['intended_url'] = '/upMVC/auth'
  ↓
Redirect → /auth → Already logged → Redirect → / ❌
```

### After Fix (Working)
```
User → /moda (not logged)
  ↓
AuthMiddleware: $_SESSION['intended_url'] = '/upMVC/moda'
  ↓
Redirect → /auth
  ↓
AuthMiddleware: $_SESSION['intended_url'] ALREADY SET, skip ✅
  ↓
User logs in
  ↓
Controller reads: $_SESSION['intended_url'] = '/upMVC/moda'
  ↓
Redirect → /moda ✅
```

## Related Files
- `d:\GitHub\upMVC\etc\Start.php` - Initializes `$this->reqURI`
- `d:\GitHub\upMVC\etc\Routes.php` - Passes `$reqURI` to dispatcher
- `d:\GitHub\upMVC\etc\Router.php` - Creates request array with `uri` key
- `d:\GitHub\upMVC\etc\Middleware\AuthMiddleware.php` - Stores intended URL
- `d:\GitHub\upMVC\modules\auth\Controller.php` - Redirects after login

## Lessons Learned
1. **Middleware runs on EVERY request** - including redirects
2. **Session variables persist** - must protect against overwrites
3. **BASE_URL is already in REQUEST_URI** - don't double-prepend
4. **Debug logging is essential** - helped identify the exact issue

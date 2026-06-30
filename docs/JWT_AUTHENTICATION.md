# JWT Authentication in upMVC

JWT support in upMVC is **opt-in**. The default session-based auth is untouched — JWT is an additional option for developers building stateless APIs, mobile backends, or SPAs alongside their web app.

Two classes handle the full JWT lifecycle:

| Class | Responsibility |
|---|---|
| `JwtService` | Issue and sign tokens (login endpoint) |
| `JwtAuthMiddleware` | Verify incoming tokens (protected routes) |

They are deliberately separate — issuance and verification are different concerns.

---

## .env Configuration

Add these keys to your `.env` (project root):

```env
JWT_SECRET=your-long-random-secret-here
JWT_ACCESS_TTL=3600        # access token lifetime in seconds (default: 1 hour)
JWT_REFRESH_TTL=2592000    # refresh token lifetime in seconds (default: 30 days)
```

`JWT_SECRET` is required. The TTL keys are optional — defaults are shown above.

---

## Issuing a Token (Login)

In your login controller, after verifying credentials:

```php
use App\Etc\JwtService;

$jwt = new JwtService();

$token = $jwt->issueAccessToken([
    'sub'      => (int) $user['id'],
    'username' => $user['username'],
    'role'     => $user['role'],
]);

// Return it to the client
header('Content-Type: application/json');
echo json_encode([
    'access_token' => $token,
    'expires_in'   => $jwt->getAccessTtl(),
]);
```

Claims are arbitrary — add whatever your controllers need to read downstream (`tenant_id`, `role`, etc.). `iat` and `exp` are added automatically.

---

## Protecting a Route

Add `['jwt']` to any route that requires a valid token:

```php
// Routes.php
$router->addRoute('/api/products',      Controller::class, 'index',  ['jwt']);
$router->addRoute('/api/products/store', Controller::class, 'store', ['jwt']);
```

The middleware reads the `Authorization: Bearer <token>` header, verifies the signature and expiry, and populates `$GLOBALS['current_user']` with the decoded payload. If the token is missing or invalid it returns a `401` JSON response immediately.

---

## Reading the Authenticated User

In any controller method on a `['jwt']`-protected route:

```php
public function index(): void
{
    $user = $GLOBALS['current_user'];

    $userId   = $user['sub'];
    $username = $user['username'];
    $role     = $user['role'];

    // ... your logic
}
```

---

## Combining JWT with Other Middleware

Named middleware can be stacked. For example, a route that requires both JWT auth and CSRF:

```php
$router->addRoute('/api/checkout', Controller::class, 'checkout', ['jwt', 'csrf']);
```

Or JWT plus rate limiting:

```php
$router->addRoute('/api/login', Controller::class, 'login', ['rate_limit']);
```

---

## Refresh Tokens (Optional)

`JwtService` can also generate opaque refresh tokens. These are random 64-char hex strings — you store a SHA-256 hash of them in your database and exchange them for new access tokens.

```php
$jwt = new JwtService();

$rawRefresh = $jwt->issueRefreshToken();             // send to client
$hashToStore = hash('sha256', $rawRefresh);          // store this in DB
$expiresAt = date('Y-m-d H:i:s', time() + $jwt->getRefreshTtl());
```

On refresh, the client sends the raw token back. You hash it, look it up in the DB, verify it's not expired or revoked, then issue a new access token and rotate the refresh token.

Refresh token storage schema (example):

```sql
CREATE TABLE refresh_tokens (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    revoked_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## Session vs JWT — When to Use Which

| | Session | JWT |
|---|---|---|
| Web app with server-rendered HTML | ✅ Recommended | Not needed |
| REST API consumed by JS / mobile | Use JWT | ✅ Recommended |
| SPA with a PHP backend | Both (session for web shell, JWT for API calls) | |
| Multi-tenant / impersonation flows | Session for web layer | JWT for API layer |

upMVC supports both simultaneously. A request to `/dashboard` goes through session auth; a request to `/api/products` goes through JWT auth. There is no conflict.

---

## Security Notes

- `JWT_SECRET` must be a long random string (32+ characters). Never commit it to version control.
- Access tokens are short-lived by design. Use refresh token rotation for long sessions.
- The signature uses HMAC-SHA256 with constant-time comparison (`hash_equals`) to prevent timing attacks.
- HTTP-only cookies are an alternative to `Authorization` headers for browser clients — implement in your controller if needed.

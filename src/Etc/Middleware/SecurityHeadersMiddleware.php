<?php
/**
 * SecurityHeadersMiddleware — baseline browser hardening headers.
 *
 * Intentionally no CSP / HSTS here: those are product- and TLS-specific.
 *
 * @package upMVC\Middleware
 */

namespace App\Etc\Middleware;

class SecurityHeadersMiddleware implements MiddlewareInterface
{
    public function handle(array $request, callable $next)
    {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        }

        return $next($request);
    }
}

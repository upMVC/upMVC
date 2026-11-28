<?php

namespace App\Etc;

/**
 * Shared TRUSTED_PROXIES parsing — exact IPs and CIDR (IPv4/IPv6).
 *
 * Used by Config::requestLooksHttps() and RateLimiter::getClientIp().
 */
final class TrustedProxies
{
    /**
     * @return list<string>
     */
    public static function list(): array
    {
        $raw = getenv('TRUSTED_PROXIES');
        if ($raw === false || $raw === '') {
            $raw = $_ENV['TRUSTED_PROXIES'] ?? '';
        }
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $raw)), static fn(string $v): bool => $v !== ''));
    }

    public static function contains(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        foreach (self::list() as $entry) {
            if (self::matches($ip, $entry)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Honour CF-Connecting-IP only when explicitly enabled.
     * Never treat it as safe just because REMOTE_ADDR is a trusted proxy
     * (Nginx/ALB will forward a client-spoofed CF header).
     */
    public static function trustCfConnectingIp(): bool
    {
        $raw = getenv('TRUST_CF_HEADER');
        if ($raw === false || $raw === '') {
            $raw = $_ENV['TRUST_CF_HEADER'] ?? '';
        }
        return filter_var((string) $raw, FILTER_VALIDATE_BOOLEAN);
    }

    private static function matches(string $ip, string $entry): bool
    {
        if ($ip === $entry) {
            return true;
        }

        if (!str_contains($entry, '/')) {
            return false;
        }

        [$network, $prefixStr] = explode('/', $entry, 2);
        if (!is_numeric($prefixStr)) {
            return false;
        }
        $prefix = (int) $prefixStr;

        $ipBin = @inet_pton($ip);
        $netBin = @inet_pton($network);
        if ($ipBin === false || $netBin === false || strlen($ipBin) !== strlen($netBin)) {
            return false;
        }

        $maxBits = strlen($ipBin) * 8;
        if ($prefix < 0 || $prefix > $maxBits) {
            return false;
        }

        $fullBytes = intdiv($prefix, 8);
        $remain = $prefix % 8;

        if ($fullBytes > 0 && substr($ipBin, 0, $fullBytes) !== substr($netBin, 0, $fullBytes)) {
            return false;
        }

        if ($remain === 0) {
            return true;
        }

        $mask = (~((1 << (8 - $remain)) - 1)) & 0xFF;
        return (ord($ipBin[$fullBytes]) & $mask) === (ord($netBin[$fullBytes]) & $mask);
    }
}

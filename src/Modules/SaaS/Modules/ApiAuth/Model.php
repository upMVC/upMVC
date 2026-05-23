<?php
namespace App\Modules\SaaS\Modules\ApiAuth;

use App\Common\Bmvc\BaseModel;

class Model extends BaseModel
{
    /** Look up a user by username — returns full row or false */
    public function findUserByUsername(string $username): array|false
    {
        $stmt = $this->conn->prepare(
            "SELECT id, username, password, name, email, tenant_id, role, state
             FROM users
             WHERE username = :username
             LIMIT 1"
        );
        $stmt->execute([':username' => trim($username)]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /** Load a user by primary key (used during token refresh) */
    public function findUserById(int $id): array|false
    {
        $stmt = $this->conn->prepare(
            "SELECT id, username, name, email, tenant_id, role
             FROM users
             WHERE id = :id
             LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Persist a hashed refresh token.
     * The raw token is never stored — only its SHA-256 hex hash.
     */
    public function storeRefreshToken(int $userId, string $tokenHash, string $expiresAt): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO refresh_tokens (user_id, token_hash, expires_at)
             VALUES (:uid, :hash, :exp)"
        );
        return $stmt->execute([':uid' => $userId, ':hash' => $tokenHash, ':exp' => $expiresAt]);
    }

    /** Find a refresh-token record by its hash */
    public function findRefreshToken(string $tokenHash): array|false
    {
        $stmt = $this->conn->prepare(
            "SELECT id, user_id, expires_at, revoked_at
             FROM refresh_tokens
             WHERE token_hash = :hash
             LIMIT 1"
        );
        $stmt->execute([':hash' => $tokenHash]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /** Mark a single token as revoked (token rotation on refresh) */
    public function revokeRefreshToken(string $tokenHash): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE refresh_tokens SET revoked_at = NOW() WHERE token_hash = :hash"
        );
        return $stmt->execute([':hash' => $tokenHash]);
    }

    /** Revoke every active refresh token for a user (logout everywhere) */
    public function revokeAllUserTokens(int $userId): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE refresh_tokens
             SET revoked_at = NOW()
             WHERE user_id = :uid AND revoked_at IS NULL"
        );
        return $stmt->execute([':uid' => $userId]);
    }
}

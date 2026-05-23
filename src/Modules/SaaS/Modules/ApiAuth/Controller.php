<?php
namespace App\Modules\SaaS\Modules\ApiAuth;

use App\Common\Bmvc\BaseApiController;
use App\Etc\JwtService;

/**
 * ApiAuth Controller — stateless JWT authentication endpoints
 *
 * Routes (no tenant middleware — these are pre-tenant):
 *   POST /api/auth/login    → returns access_token + refresh_token
 *   POST /api/auth/refresh  → rotates tokens (old refresh revoked, new pair issued)
 *   POST /api/auth/logout   → revokes refresh token(s)  [requires jwt middleware]
 */
class Controller extends BaseApiController
{
    // -----------------------------------------------------------------------
    // POST /api/auth/login
    // -----------------------------------------------------------------------

    public function login(): never
    {
        $body  = $this->requireFields(['username', 'password']);
        $model = new Model();

        $user = $model->findUserByUsername($body['username']);

        if (!$user || !password_verify($body['password'], $user['password'])) {
            $this->error('Invalid credentials', 401);
        }

        if ((int) $user['state'] !== 1) {
            $this->error('Account is not activated', 403);
        }

        [$accessToken, $rawRefresh] = $this->buildTokenPair($model, $user);

        $this->success([
            'access_token'  => $accessToken,
            'refresh_token' => $rawRefresh,
            'token_type'    => 'Bearer',
            'expires_in'    => (new JwtService())->getAccessTtl(),
            'user'          => [
                'id'        => (int) $user['id'],
                'username'  => $user['username'],
                'name'      => $user['name'],
                'tenant_id' => (int) $user['tenant_id'],
                'role'      => $user['role'] ?? 'tenant_user',
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /api/auth/refresh
    // -----------------------------------------------------------------------

    public function refresh(): never
    {
        $body      = $this->requireFields(['refresh_token']);
        $tokenHash = hash('sha256', $body['refresh_token']);
        $model     = new Model();

        $record = $model->findRefreshToken($tokenHash);

        if (!$record) {
            $this->error('Invalid refresh token', 401);
        }
        if ($record['revoked_at'] !== null) {
            // Possible token theft — revoke all tokens for this user
            $model->revokeAllUserTokens((int) $record['user_id']);
            $this->error('Refresh token has already been used (possible theft detected)', 401);
        }
        if (strtotime($record['expires_at']) < time()) {
            $this->error('Refresh token has expired — please log in again', 401);
        }

        // Rotate: invalidate old token, issue new pair
        $model->revokeRefreshToken($tokenHash);

        $user = $model->findUserById((int) $record['user_id']);
        if (!$user) {
            $this->error('User not found', 401);
        }

        [$accessToken, $rawRefresh] = $this->buildTokenPair($model, $user);

        $this->success([
            'access_token'  => $accessToken,
            'refresh_token' => $rawRefresh,
            'token_type'    => 'Bearer',
            'expires_in'    => (new JwtService())->getAccessTtl(),
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /api/auth/logout  [requires jwt middleware]
    // -----------------------------------------------------------------------

    public function logout(): never
    {
        $body  = $this->body();
        $model = new Model();

        if (!empty($body['refresh_token'])) {
            // Revoke the single supplied refresh token
            $model->revokeRefreshToken(hash('sha256', $body['refresh_token']));
        } else {
            // No specific token supplied — revoke all sessions for this user
            $userId = (int) ($this->user['sub'] ?? 0);
            if ($userId > 0) {
                $model->revokeAllUserTokens($userId);
            }
        }

        $this->success(null, 'Logged out successfully');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Issue an access token + refresh token pair and persist the refresh token.
     *
     * @param Model $model
     * @param array $user  Row from the users table
     * @return array{string, string}  [accessToken, rawRefreshToken]
     */
    private function buildTokenPair(Model $model, array $user): array
    {
        $jwt = new JwtService();

        $accessToken = $jwt->issueAccessToken([
            'sub'       => (int) $user['id'],
            'username'  => $user['username'],
            'tenant_id' => (int) $user['tenant_id'],
            'role'      => $user['role'] ?? 'tenant_user',
        ]);

        $rawRefresh = $jwt->issueRefreshToken();
        $expiresAt  = date('Y-m-d H:i:s', time() + $jwt->getRefreshTtl());

        $model->storeRefreshToken(
            (int) $user['id'],
            hash('sha256', $rawRefresh),
            $expiresAt
        );

        return [$accessToken, $rawRefresh];
    }
}

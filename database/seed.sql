-- ============================================================
-- seed.sql — demo data for a fresh upMVC install
--
-- Import AFTER the schema:
--   mysql -u root -p your_db < database/schema.sql
--   mysql -u root -p your_db < database/seed.sql
--
-- Kernel-only data: just `users`. Multi-tenant demo data (tenants,
-- plans, refresh_tokens) ships with the SaaS pack — see
-- bitshost/upmvc-saas-pack → database/demo.sql.
--
-- Hand-maintained, unlike schema.sql. Safe to re-import: it clears
-- the demo rows first.
--
-- ------------------------------------------------------------
-- LOGINS
--   admin / Admin5678!     platform_admin, active
--   demo  / Test1234!      tenant_user,    active
--   ghost / Test1234!      tenant_user,    INACTIVE (activation flow)
--
-- Demo credentials only. Never load this into production.
-- ============================================================

DELETE FROM users WHERE username IN ('admin', 'demo', 'ghost');

-- password 'Admin5678!' → $2y$10$WMvYt7KDx5epitl/W8wAtuPmZs7wbOkU1Cix0nkcyYFSJGl5H.KQq
-- password 'Test1234!'  → $2y$10$JR/tLsIt6ZXtCsRdLgVb/OjoxQ7LsVoTrLxrUWJVmzNEwTKqsiRfS

INSERT INTO users (tenant_id, name, username, email, password, token, state, role) VALUES
    (NULL, 'Platform Admin', 'admin', 'admin@upmvc.dev',
     '$2y$10$WMvYt7KDx5epitl/W8wAtuPmZs7wbOkU1Cix0nkcyYFSJGl5H.KQq',
     '', 1, 'platform_admin'),

    (NULL, 'Demo User', 'demo', 'demo@upmvc.dev',
     '$2y$10$JR/tLsIt6ZXtCsRdLgVb/OjoxQ7LsVoTrLxrUWJVmzNEwTKqsiRfS',
     '', 1, 'tenant_user'),

    -- inactive: never clicked the activation link — useful for testing that flow
    (NULL, 'Ghost Account', 'ghost', 'ghost@upmvc.dev',
     '$2y$10$JR/tLsIt6ZXtCsRdLgVb/OjoxQ7LsVoTrLxrUWJVmzNEwTKqsiRfS',
     'activation-token-abc123', 0, 'tenant_user');

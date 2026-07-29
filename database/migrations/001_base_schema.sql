-- ============================================================
-- Migration 001 — Base schema
-- Creates the users table (utf8mb4, bcrypt password, SaaS columns)
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id`        INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT              NULL     DEFAULT NULL    COMMENT 'NULL = platform admin',
    `name`      VARCHAR(255)     NOT NULL DEFAULT '',
    `email`     VARCHAR(255)     NOT NULL DEFAULT '',
    `username`  VARCHAR(100)     NOT NULL,
    `password`  VARCHAR(255)     NOT NULL                 COMMENT 'bcrypt hash',
    `token`     VARCHAR(64)      NOT NULL DEFAULT '',
    `state`     TINYINT(1)       NOT NULL DEFAULT 0       COMMENT '0=inactive, 1=active',
    `role`      VARCHAR(30)      NOT NULL DEFAULT 'tenant_user'
                                          COMMENT 'platform_admin | tenant_owner | tenant_user',
    `stamp`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_username (username),
    KEY idx_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- schema.sql — GENERATED FILE, DO NOT EDIT BY HAND
--
-- Produced by: php src/Tools/migrate.php --dump
-- Generated:   2026-07-29 12:24:13
--
-- Full current structure, for fresh installs and for reading the
-- data model without running anything. The migrations remain the
-- source of truth; this file is derived from them.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ---------- users ----------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) DEFAULT NULL COMMENT 'NULL = platform admin',
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'bcrypt hash',
  `token` varchar(64) NOT NULL DEFAULT '',
  `state` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=inactive, 1=active',
  `role` varchar(30) NOT NULL DEFAULT 'tenant_user' COMMENT 'platform_admin | tenant_owner | tenant_user',
  `stamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

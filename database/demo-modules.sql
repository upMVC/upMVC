-- ============================================================
-- demo-modules.sql — tables the bundled demo modules need
--
-- OPTIONAL. The framework itself needs none of this: database/schema.sql
-- covers the kernel (`users`). This file exists so the demo modules that
-- ship with a `composer create-project` install actually run.
--
--   mysql -u root -p your_db < database/demo-modules.sql
--
-- Without it, /admin fails with:
--   SQLSTATE[42S02]: Base table or view not found: 1146
--   Table 'your_db.usernou' doesn't exist
--
-- Safe to re-import: CREATE TABLE IF NOT EXISTS and INSERT IGNORE throughout.
--
-- Delete a demo module and its table here becomes dead weight — drop it.
--
-- ------------------------------------------------------------
-- Not covered here:
--   DashboardExample  creates dashboard_users / dashboard_settings itself,
--                     at runtime, on first visit — nothing to import.
--   Test* modules     reference testapis, testauths, testbasics, testcruds,
--                     testdashboards, testitemss and testparents, for which
--                     no definition exists anywhere in the project.
--
-- Demo logins below use the password: password
-- Demo data only. Never load this into production.
-- ============================================================

-- -----------------------------------------------
-- usernou — used by the Admin module (/admin) and the Reactnb demo
--
-- Named separately from the kernel's `users` table on purpose: it is the
-- demo CRUD target, so experimenting with it cannot break authentication.
-- -----------------------------------------------

CREATE TABLE IF NOT EXISTS `usernou` (
    `id`         int(11)      NOT NULL AUTO_INCREMENT,
    `username`   varchar(50)  NOT NULL,
    `email`      varchar(100) NOT NULL,
    `password`   varchar(255) NOT NULL,
    `fullname`   varchar(100) DEFAULT NULL,
    `created_at` timestamp    NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp    NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_username` (`username`),
    UNIQUE KEY `unique_email`    (`email`),
    KEY `idx_username` (`username`),
    KEY `idx_email`    (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- password 'password' → $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT IGNORE INTO `usernou` (`username`, `email`, `password`, `fullname`) VALUES
    ('admin',   'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator'),
    ('johndoe', 'john@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe'),
    ('janedoe', 'jane@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Doe');

-- -----------------------------------------------
-- products — used by the Product module (/product)
--
-- The original module file used INSERT INTO, so re-importing it duplicated
-- every row. INSERT IGNORE with explicit ids makes this idempotent.
-- -----------------------------------------------

CREATE TABLE IF NOT EXISTS `products` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(255)   NOT NULL,
    `description` TEXT           NOT NULL,
    `price`       DECIMAL(10, 2) NOT NULL,
    `status`      ENUM('active','inactive') NOT NULL,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `products` (`id`, `name`, `description`, `price`, `status`) VALUES
    (1, 'Sample Product 1', 'This is a sample product description', 99.99,  'active'),
    (2, 'Sample Product 2', 'Another sample product',               149.50, 'active'),
    (3, 'Sample Product 3', 'Inactive product example',             75.00,  'inactive');

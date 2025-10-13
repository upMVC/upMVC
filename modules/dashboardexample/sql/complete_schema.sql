/*
 * Complete Dashboard System Schema
 * Created for upMVC Dashboard Example
 * 
 * This schema includes:
 * - User management with roles and permissions
 * - Blog/News system with categories and tags
 * - Pages management with SEO settings
 * - Dashboard settings and configurations
 */

-- ====================================
-- USER MANAGEMENT SYSTEM
-- ====================================

-- Enhanced Users table with more fields
CREATE TABLE IF NOT EXISTS dash_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'editor', 'author', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive', 'banned', 'pending') DEFAULT 'pending',
    avatar VARCHAR(255) NULL,
    bio TEXT NULL,
    phone VARCHAR(20) NULL,
    last_login DATETIME NULL,
    email_verified_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Permissions table for granular control
CREATE TABLE IF NOT EXISTS dash_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    module VARCHAR(50) NOT NULL, -- 'users', 'blog', 'pages', 'dashboard'
    action VARCHAR(50) NOT NULL, -- 'create', 'read', 'update', 'delete', 'manage'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role Permissions mapping
CREATE TABLE IF NOT EXISTS dash_role_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role ENUM('super_admin', 'admin', 'editor', 'author', 'user'),
    permission_id INT,
    FOREIGN KEY (permission_id) REFERENCES dash_permissions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (role, permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- BLOG/NEWS SYSTEM
-- ====================================

-- Blog Categories
CREATE TABLE IF NOT EXISTS dash_blog_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    parent_id INT NULL,
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES dash_blog_categories(id) ON DELETE SET NULL,
    INDEX idx_parent (parent_id),
    INDEX idx_status (status),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Tags
CREATE TABLE IF NOT EXISTS dash_blog_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    color VARCHAR(7) DEFAULT '#007cba', -- Hex color code
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Posts
CREATE TABLE IF NOT EXISTS dash_blog_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT NULL,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255) NULL,
    status ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    author_id INT NOT NULL,
    category_id INT NULL,
    views_count INT DEFAULT 0,
    likes_count INT DEFAULT 0,
    comments_enabled BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    published_at DATETIME NULL,
    scheduled_at DATETIME NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    meta_keywords TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES dash_users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES dash_blog_categories(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_author (author_id),
    INDEX idx_category (category_id),
    INDEX idx_slug (slug),
    INDEX idx_published (published_at),
    INDEX idx_featured (is_featured),
    FULLTEXT KEY ft_search (title, excerpt, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Post Tags (Many-to-Many)
CREATE TABLE IF NOT EXISTS dash_blog_post_tags (
    post_id INT,
    tag_id INT,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES dash_blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES dash_blog_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Comments
CREATE TABLE IF NOT EXISTS dash_blog_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    parent_id INT NULL, -- For threaded comments
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(255) NOT NULL,
    author_website VARCHAR(255) NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam', 'trash') DEFAULT 'pending',
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES dash_blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES dash_blog_comments(id) ON DELETE CASCADE,
    INDEX idx_post (post_id),
    INDEX idx_status (status),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- PAGES MANAGEMENT SYSTEM
-- ====================================

-- Static Pages
CREATE TABLE IF NOT EXISTS dash_pages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    excerpt TEXT NULL,
    featured_image VARCHAR(255) NULL,
    template VARCHAR(100) DEFAULT 'default', -- Template file to use
    status ENUM('draft', 'published', 'private', 'archived') DEFAULT 'draft',
    author_id INT NOT NULL,
    parent_id INT NULL, -- For page hierarchy
    sort_order INT DEFAULT 0,
    is_homepage BOOLEAN DEFAULT FALSE,
    show_in_menu BOOLEAN DEFAULT TRUE,
    menu_title VARCHAR(100) NULL, -- Different title for menu
    password VARCHAR(255) NULL, -- For password-protected pages
    
    -- SEO fields
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    meta_keywords TEXT NULL,
    og_title VARCHAR(255) NULL,
    og_description TEXT NULL,
    og_image VARCHAR(255) NULL,
    
    -- Timestamps
    published_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES dash_users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES dash_pages(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_slug (slug),
    INDEX idx_parent (parent_id),
    INDEX idx_menu (show_in_menu),
    INDEX idx_homepage (is_homepage),
    FULLTEXT KEY ft_search (title, content, excerpt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Page Custom Fields (for flexible content)
CREATE TABLE IF NOT EXISTS dash_page_meta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    page_id INT NOT NULL,
    meta_key VARCHAR(255) NOT NULL,
    meta_value LONGTEXT NULL,
    FOREIGN KEY (page_id) REFERENCES dash_pages(id) ON DELETE CASCADE,
    INDEX idx_page_key (page_id, meta_key),
    INDEX idx_key (meta_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- DASHBOARD SETTINGS & CONFIGURATION
-- ====================================

-- Enhanced Dashboard Settings
CREATE TABLE IF NOT EXISTS dash_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_group VARCHAR(50) NOT NULL, -- 'general', 'appearance', 'blog', 'pages', 'users', 'security'
    setting_key VARCHAR(100) NOT NULL,
    setting_value LONGTEXT NULL,
    setting_type ENUM('string', 'number', 'boolean', 'json', 'array') DEFAULT 'string',
    description TEXT NULL,
    is_autoload BOOLEAN DEFAULT TRUE, -- Load on every request
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_key (setting_group, setting_key),
    INDEX idx_group (setting_group),
    INDEX idx_autoload (is_autoload)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dashboard Widgets/Modules configuration
CREATE TABLE IF NOT EXISTS dash_widgets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    widget_type ENUM('stats', 'chart', 'list', 'custom') DEFAULT 'stats',
    position VARCHAR(20) DEFAULT 'main', -- 'main', 'sidebar', 'top', 'bottom'
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    config JSON NULL, -- Widget-specific configuration
    permissions JSON NULL, -- Required permissions to view
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_position (position),
    INDEX idx_active (is_active),
    INDEX idx_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Log for dashboard actions
CREATE TABLE IF NOT EXISTS dash_activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL, -- 'created', 'updated', 'deleted', 'login', 'logout'
    module VARCHAR(50) NOT NULL, -- 'users', 'blog', 'pages', 'dashboard'
    record_id INT NULL, -- ID of the affected record
    description TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES dash_users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_module (module),
    INDEX idx_action (action),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- MEDIA/FILE MANAGEMENT
-- ====================================

-- Media Library for file uploads
CREATE TABLE IF NOT EXISTS dash_media (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL, -- in bytes
    mime_type VARCHAR(100) NOT NULL,
    file_type ENUM('image', 'document', 'video', 'audio', 'other') DEFAULT 'other',
    width INT NULL, -- for images
    height INT NULL, -- for images
    alt_text VARCHAR(255) NULL,
    caption TEXT NULL,
    description TEXT NULL,
    uploaded_by INT NOT NULL,
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES dash_users(id) ON DELETE CASCADE,
    INDEX idx_type (file_type),
    INDEX idx_uploader (uploaded_by),
    INDEX idx_public (is_public),
    FULLTEXT KEY ft_search (original_name, alt_text, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- INSERT DEFAULT DATA
-- ====================================

-- Insert default permissions
INSERT INTO dash_permissions (name, description, module, action) VALUES
-- Dashboard permissions
('dashboard.view', 'View dashboard', 'dashboard', 'read'),
('dashboard.manage', 'Manage dashboard settings', 'dashboard', 'manage'),

-- User permissions
('users.view', 'View users', 'users', 'read'),
('users.create', 'Create users', 'users', 'create'),
('users.edit', 'Edit users', 'users', 'update'),
('users.delete', 'Delete users', 'users', 'delete'),
('users.manage', 'Full user management', 'users', 'manage'),

-- Blog permissions
('blog.view', 'View blog posts', 'blog', 'read'),
('blog.create', 'Create blog posts', 'blog', 'create'),
('blog.edit', 'Edit blog posts', 'blog', 'update'),
('blog.delete', 'Delete blog posts', 'blog', 'delete'),
('blog.publish', 'Publish blog posts', 'blog', 'manage'),
('blog.categories', 'Manage categories', 'blog', 'manage'),
('blog.comments', 'Manage comments', 'blog', 'manage'),

-- Pages permissions
('pages.view', 'View pages', 'pages', 'read'),
('pages.create', 'Create pages', 'pages', 'create'),
('pages.edit', 'Edit pages', 'pages', 'update'),
('pages.delete', 'Delete pages', 'pages', 'delete'),
('pages.publish', 'Publish pages', 'pages', 'manage'),

-- Media permissions
('media.view', 'View media library', 'media', 'read'),
('media.upload', 'Upload files', 'media', 'create'),
('media.edit', 'Edit media', 'media', 'update'),
('media.delete', 'Delete media', 'media', 'delete');

-- Assign permissions to roles
INSERT INTO dash_role_permissions (role, permission_id) 
SELECT 'super_admin', id FROM dash_permissions; -- Super admin gets all permissions

INSERT INTO dash_role_permissions (role, permission_id) 
SELECT 'admin', id FROM dash_permissions WHERE module IN ('dashboard', 'users', 'blog', 'pages', 'media');

INSERT INTO dash_role_permissions (role, permission_id) 
SELECT 'editor', id FROM dash_permissions WHERE module IN ('blog', 'pages', 'media') AND action != 'delete';

INSERT INTO dash_role_permissions (role, permission_id) 
SELECT 'author', id FROM dash_permissions WHERE module IN ('blog', 'media') AND action IN ('read', 'create', 'update');

-- Create default super admin user (password: admin123)
INSERT INTO dash_users (username, email, password, first_name, last_name, role, status, email_verified_at) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super', 'Admin', 'super_admin', 'active', NOW());

-- Insert default dashboard settings
INSERT INTO dash_settings (setting_group, setting_key, setting_value, setting_type, description) VALUES
('general', 'site_name', 'upMVC Dashboard', 'string', 'Site name displayed in dashboard'),
('general', 'site_description', 'Powerful dashboard built with upMVC', 'string', 'Site description'),
('general', 'admin_email', 'admin@example.com', 'string', 'Administrator email'),
('general', 'timezone', 'UTC', 'string', 'Default timezone'),
('general', 'date_format', 'Y-m-d', 'string', 'Date format'),
('general', 'time_format', 'H:i:s', 'string', 'Time format'),

('appearance', 'theme', 'light', 'string', 'Dashboard theme'),
('appearance', 'primary_color', '#007cba', 'string', 'Primary theme color'),
('appearance', 'logo_url', '', 'string', 'Dashboard logo URL'),
('appearance', 'favicon_url', '', 'string', 'Favicon URL'),

('blog', 'posts_per_page', '10', 'number', 'Posts per page'),
('blog', 'allow_comments', 'true', 'boolean', 'Allow comments on posts'),
('blog', 'moderate_comments', 'true', 'boolean', 'Moderate comments before publishing'),
('blog', 'default_category', '1', 'number', 'Default category for new posts'),

('pages', 'default_template', 'default', 'string', 'Default page template'),
('pages', 'show_pages_in_menu', 'true', 'boolean', 'Show pages in navigation menu'),

('users', 'allow_registration', 'false', 'boolean', 'Allow user registration'),
('users', 'default_role', 'user', 'string', 'Default role for new users'),
('users', 'require_email_verification', 'true', 'boolean', 'Require email verification'),

('security', 'session_timeout', '3600', 'number', 'Session timeout in seconds'),
('security', 'max_login_attempts', '5', 'number', 'Maximum login attempts'),
('security', 'enable_two_factor', 'false', 'boolean', 'Enable two-factor authentication');

-- Insert default blog category
INSERT INTO dash_blog_categories (name, slug, description, status) VALUES
('Uncategorized', 'uncategorized', 'Default category for blog posts', 'active'),
('News', 'news', 'Latest news and updates', 'active'),
('Tutorials', 'tutorials', 'Step-by-step tutorials', 'active');

-- Insert default tags
INSERT INTO dash_blog_tags (name, slug, description, color) VALUES
('upMVC', 'upmvc', 'Posts about upMVC framework', '#007cba'),
('PHP', 'php', 'PHP related content', '#777bb4'),
('Tutorial', 'tutorial', 'Tutorial content', '#28a745'),
('News', 'news', 'News and announcements', '#ffc107');

-- Insert default dashboard widgets
INSERT INTO dash_widgets (name, title, description, widget_type, position, sort_order, config) VALUES
('user_stats', 'User Statistics', 'Display user registration statistics', 'stats', 'main', 1, '{"show_graph": true, "period": "month"}'),
('blog_stats', 'Blog Statistics', 'Display blog post statistics', 'stats', 'main', 2, '{"show_graph": true, "period": "month"}'),
('recent_posts', 'Recent Posts', 'List of recently created blog posts', 'list', 'sidebar', 1, '{"limit": 5}'),
('recent_users', 'New Users', 'List of recently registered users', 'list', 'sidebar', 2, '{"limit": 5}'),
('system_info', 'System Information', 'Display system information', 'stats', 'bottom', 1, '{"show_php_version": true, "show_mysql_version": true}');

-- Insert sample blog post
INSERT INTO dash_blog_posts (title, slug, excerpt, content, status, author_id, category_id, published_at, meta_title, meta_description) VALUES
('Welcome to upMVC Dashboard', 'welcome-to-upmvc-dashboard', 'This is your first blog post in the new dashboard system.', 
'<h2>Welcome to the upMVC Dashboard System</h2>\n<p>This is a comprehensive dashboard system built with the upMVC framework. It demonstrates the power and flexibility of modular PHP development.</p>\n<h3>Features</h3>\n<ul>\n<li>User management with roles and permissions</li>\n<li>Blog system with categories and tags</li>\n<li>Pages management with SEO support</li>\n<li>Media library for file management</li>\n<li>Activity logging and security</li>\n</ul>\n<p>Explore the different sections to see what you can accomplish with upMVC!</p>', 
'published', 1, 1, NOW(), 'Welcome to upMVC Dashboard', 'Learn about the powerful dashboard system built with upMVC framework');

-- Link the sample post with tags
INSERT INTO dash_blog_post_tags (post_id, tag_id) VALUES (1, 1), (1, 3);

-- Insert sample page
INSERT INTO dash_pages (title, slug, content, status, author_id, show_in_menu, meta_title, meta_description, published_at) VALUES
('About', 'about', '<h1>About Our Dashboard</h1>\n<p>This dashboard system showcases the capabilities of the upMVC framework. Built with modular architecture, it provides a comprehensive content management solution.</p>\n<h2>Key Features</h2>\n<ul>\n<li>Modular design</li>\n<li>Role-based permissions</li>\n<li>SEO-friendly</li>\n<li>Responsive design</li>\n<li>Built with modern web standards</li>\n</ul>', 
'published', 1, TRUE, 'About Our Dashboard', 'Learn about our comprehensive dashboard system built with upMVC', NOW()),
('Contact', 'contact', '<h1>Contact Us</h1>\n<p>Get in touch with our team for support or inquiries.</p>\n<p>Email: admin@example.com</p>', 
'published', 1, TRUE, 'Contact Us', 'Get in touch with our team', NOW());

-- Log the initial setup
INSERT INTO dash_activity_log (user_id, action, module, description) VALUES
(1, 'setup', 'dashboard', 'Initial dashboard system setup completed');
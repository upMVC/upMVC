<?php
/*
 *   Created on October 13, 2025
 *   Enhanced Modern BaseView for upMVC NoFramework
 *   Copyright (c) 2023-2025 BitsHost
 *   All rights reserved.
 */

namespace Common\Bmvc;

use Common\Bmvc\ModernCss;

class BaseViewModern
{
    protected $globals = [
        'settings' => [
            'theme' => 'modern-dark',
            'site_name' => 'upMVC NoFramework',
            'items_per_page' => '12',
            'maintenance_mode' => 'false',
            'version' => '2.0',
            'logo_text' => '🚀 upMVC'
        ]
    ];

    /**
     * Add a global variable accessible to all views
     */
    public function addGlobal($key, $value) {
        if ($key === 'settings' && isset($this->globals['settings'])) {
            $this->globals['settings'] = array_merge($this->globals['settings'], $value);
        } else {
            $this->globals[$key] = $value;
        }
    }

    /**
     * Get a global variable
     */
    public function getGlobal($key) {
        return $this->globals[$key] ?? null;
    }

    /**
     * Modern responsive navigation menu
     */
    public function menu()
    {
        $siteName = $this->globals['settings']['site_name'];
        $logoText = $this->globals['settings']['logo_text'];
?>
        <nav class="modern-nav compact">
            <div class="nav-container">
                <div class="nav-brand">
                    <a href="<?php echo BASE_URL; ?>" class="brand-link">
                        <span class="brand-logo"><?php echo $logoText; ?></span>
                        <span class="brand-text"><?php echo $siteName; ?></span>
                    </a>
                </div>
                
                <!-- Compact horizontal menu for desktop -->
                <div class="nav-main-menu">
                    <a href="<?php echo BASE_URL; ?>/test" class="nav-main-link">🧪 Tests</a>
                    <a href="<?php echo BASE_URL; ?>/test/modern" class="nav-main-link nav-highlight">✨ Modern</a>
                    <a href="<?php echo BASE_URL; ?>/users" class="nav-main-link">👥 CRUD</a>
                    <a href="<?php echo BASE_URL; ?>/react" class="nav-main-link">⚛️ React</a>
                    
                    <!-- More dropdown -->
                    <div class="nav-dropdown">
                        <button class="nav-dropdown-toggle">More ▼</button>
                        <div class="nav-dropdown-menu">
                            <a href="<?php echo BASE_URL; ?>/test-one" class="dropdown-link">📝 Single Param</a>
                            <a href="<?php echo BASE_URL; ?>/test-one/two" class="dropdown-link">📋 Multi Param</a>
                            <a href="<?php echo BASE_URL; ?>/newmod" class="dropdown-link">⚡ Enhanced CRUD</a>
                            <a href="<?php echo BASE_URL; ?>/reactb" class="dropdown-link">� React Alt</a>
                            <a href="<?php echo BASE_URL; ?>/admin" class="dropdown-link">🚫 No-Build JS</a>
                            <div class="dropdown-divider"></div>
                            <a href="https://github.com/BitsHost/upMVC" target="_blank" class="dropdown-link">💻 GitHub</a>
                            <a href="https://upmvc.com" target="_blank" class="dropdown-link">🌐 Official Site</a>
                        </div>
                    </div>
                </div>
                
                <!-- Right side actions -->
                <div class="nav-actions">
                    <?php if (isset($_SESSION["logged"]) && $_SESSION["logged"] == true): ?>
                    <a href="<?php echo BASE_URL; ?>/logout" class="nav-action-btn logout-btn">
                        🚪 Logout
                    </a>
                    <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/auth" class="nav-action-btn login-btn">
                        🔑 Login
                    </a>
                    <?php endif; ?>
                    
                    <button class="nav-action-btn theme-toggle" onclick="toggleDarkMode()" title="Toggle Dark Mode">
                        <span class="theme-icon">🌙</span>
                    </button>
                </div>
                
                <!-- Mobile menu toggle -->
                <button class="nav-toggle" id="navToggle">
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                </button>

                <!-- Mobile menu (simplified) -->
                <div class="nav-menu mobile-only" id="navMenu">
                    <a href="<?php echo BASE_URL; ?>/test" class="nav-mobile-link">🧪 Basic Test</a>
                    <a href="<?php echo BASE_URL; ?>/test/modern" class="nav-mobile-link">✨ Modern Test</a>
                    <a href="<?php echo BASE_URL; ?>/users" class="nav-mobile-link">👥 Users CRUD</a>
                    <a href="<?php echo BASE_URL; ?>/react" class="nav-mobile-link">⚛️ React Islands</a>
                    <a href="<?php echo BASE_URL; ?>/auth" class="nav-mobile-link">🔑 Authentication</a>
                    <div class="nav-divider"></div>
                    <a href="https://github.com/BitsHost/upMVC" target="_blank" class="nav-mobile-link">� GitHub</a>
                    <a href="https://upmvc.com" target="_blank" class="nav-mobile-link">🌐 Official Site</a>
                </div>
            </div>
        </nav>
<?php
    }

    /**
     * Modern HTML head with enhanced meta tags and styling
     */
    public function startHead($title)
    {
        $siteName = $this->globals['settings']['site_name'];
        $version = $this->globals['settings']['version'];
        $newCss = new ModernCss();
?>
<!DOCTYPE html>
<html lang="en" class="modern-theme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="<?php echo $siteName; ?> - Modern PHP NoFramework for rapid development">
    <meta name="keywords" content="PHP, NoFramework, upMVC, Modular, MVC, Development">
    <meta name="author" content="BitsHost">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo $title . ' - ' . $siteName; ?>">
    <meta property="og:description" content="Modern PHP NoFramework for rapid development">
    <meta property="og:type" content="website">
    
    <title><?php echo $title . ' - ' . $siteName; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚀</text></svg>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Custom Modern Styles (includes utility classes) -->
    <?php $newCss->modernCss(); ?>
    
    <!-- Core JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<?php
    }

    /**
     * End head section and start navigation
     */
    public function endHead()
    {
        echo "</head>\n";
        $this->menu();
    }

    /**
     * Modern body with enhanced layout
     */
    public function startBody($title)
    {
        $version = $this->globals['settings']['version'];
?>
<body class="modern-body" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">
    
    <!-- Demo Notice Banner -->
    <div class="demo-banner">
        <div class="demo-content">
            <span class="demo-icon">🔐</span>
            <span>Demo credentials: <strong>user: demo</strong> | <strong>pass: demo</strong></span>
            <span class="demo-version">v<?php echo $version; ?></span>
        </div>
    </div>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="content-container">
            <header class="page-header">
                <h1 class="page-title">
                    <span class="title-accent">✨</span>
                    <?php echo $title; ?>
                </h1>
                <div class="page-controls">
                    <button @click="darkMode = !darkMode" class="theme-toggle">
                        <span x-show="!darkMode">🌙</span>
                        <span x-show="darkMode">☀️</span>
                    </button>
                </div>
            </header>
            
            <div class="page-content">
<?php
    }

    /**
     * End body content
     */
    public function endBody()
    {
?>
            </div>
        </div>
    </main>
<?php
    }

    /**
     * Modern footer with enhanced information
     */
    public function startFooter()
    {
        $currentYear = date("Y");
        $version = $this->globals['settings']['version'];
?>
    <footer class="modern-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h4 class="footer-title">🚀 upMVC NoFramework</h4>
                <p class="footer-description">
                    Modern PHP development without constraints. 
                    Built for developers who value freedom and performance.
                </p>
                <div class="footer-stats">
                    <span class="stat-item">
                        <span class="stat-icon">⚡</span>
                        <span>Lightning Fast</span>
                    </span>
                    <span class="stat-item">
                        <span class="stat-icon">🗂️</span>
                        <span>Modular</span>
                    </span>
                    <span class="stat-item">
                        <span class="stat-icon">🔓</span>
                        <span>Freedom</span>
                    </span>
                </div>
            </div>
            
            <div class="footer-section">
                <h4 class="footer-title">🔗 Quick Links</h4>
                <div class="footer-links">
                    <a href="https://upmvc.com" target="_blank" class="footer-link">
                        <span class="link-icon">🌐</span>Official Website
                    </a>
                    <a href="https://github.com/BitsHost/upMVC" target="_blank" class="footer-link">
                        <span class="link-icon">💻</span>GitHub Repository
                    </a>
                    <a href="https://bitshost.biz/free-web-hosting.html" target="_blank" class="footer-link">
                        <span class="link-icon">☁️</span>Free Hosting
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4 class="footer-title">💡 Philosophy</h4>
                <blockquote class="footer-quote">
                    "PHP NoFrameworks all suck!" - <em>Rasmus Lerdorf</em>
                </blockquote>
                <p class="footer-philosophy">
                    That's why upMVC is a <strong>NoFramework</strong> - 
                    giving you tools without constraints.
                </p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <span class="copyright">
                    ©️ <?php echo $currentYear; ?> BitsHost Cloud - All rights reserved
                </span>
                <span class="version-info">
                    upMVC NoFramework v<?php echo $version; ?>
                </span>
                <div class="footer-badges">
                    <span class="badge">PHP 8.1+</span>
                    <span class="badge">PSR-4</span>
                    <span class="badge">MIT License</span>
                </div>
            </div>
        </div>
    </footer>
<?php
    }

    /**
     * Close HTML document
     */
    public function endFooter()
    {
?>
    <!-- Modern Navigation & Theme JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const navToggle = document.getElementById('navToggle');
            const navMenu = document.getElementById('navMenu');
            
            if (navToggle && navMenu) {
                navToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    navMenu.classList.toggle('active');
                    this.classList.toggle('active');
                });
            }
            
            // Close mobile menu when clicking outside
            document.addEventListener('click', function(e) {
                if (navToggle && navMenu && 
                    !navToggle.contains(e.target) && 
                    !navMenu.contains(e.target)) {
                    navMenu.classList.remove('active');
                    navToggle.classList.remove('active');
                }
            });
            
            // Dark mode initialization
            function initDarkMode() {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const savedTheme = localStorage.getItem('darkMode');
                const isDark = savedTheme ? savedTheme === 'true' : prefersDark;
                
                if (isDark) {
                    document.documentElement.classList.add('dark');
                }
                updateThemeIcon();
            }
            
            function updateThemeIcon() {
                const isDark = document.documentElement.classList.contains('dark');
                const themeIcon = document.querySelector('.theme-icon');
                if (themeIcon) {
                    themeIcon.textContent = isDark ? '☀️' : '🌙';
                }
            }
            
            // Initialize dark mode
            initDarkMode();
            
            // Listen for system theme changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (!localStorage.getItem('darkMode')) {
                    if (e.matches) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                    updateThemeIcon();
                }
            });
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });
        });
        
        // Global function for theme toggle (called from button)
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            const isDark = document.documentElement.classList.contains('dark');
            localStorage.setItem('darkMode', isDark);
            
            const themeIcon = document.querySelector('.theme-icon');
            if (themeIcon) {
                themeIcon.textContent = isDark ? '☀️' : '🌙';
            }
        }
    </script>
</body>
</html>
<?php
    }
}
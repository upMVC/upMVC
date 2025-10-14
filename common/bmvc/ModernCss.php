<?php
/*
 *   Created on October 13, 2025
 *   Modern CSS Framework for upMVC NoFramework
 *   Copyright (c) 2023-2025 BitsHost
 */

namespace Common\Bmvc;

/**
 * Modern CSS Framework - Contemporary design system with compact navigation
 */
class ModernCss
{
    /**
     * Output modern CSS framework
     */
    public function modernCss()
    {
?>
<style>
/* ============================================
   MODERN CSS FRAMEWORK - upMVC v2.0
   Contemporary design with compact navigation
   ============================================ */

/* CSS Custom Properties for theming */
:root {
    --primary: #667eea;
    --primary-dark: #5a67d8;
    --secondary: #764ba2;
    --accent: #f093fb;
    --success: #43e97b;
    --warning: #fbbf24;
    --error: #f87171;
    
    /* Light theme colors */
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-tertiary: #f1f5f9;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --text-muted: #9ca3af;
    --border: #e5e7eb;
    --border-light: #f3f4f6;
    --shadow: rgba(0, 0, 0, 0.1);
    --shadow-lg: rgba(0, 0, 0, 0.15);
    
    /* Navigation specific */
    --nav-height: 60px;
    --nav-bg: rgba(255, 255, 255, 0.95);
    --nav-border: rgba(226, 232, 240, 0.8);
    
    /* Typography */
    --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --font-mono: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
    
    /* Spacing */
    --space-xs: 0.25rem;
    --space-sm: 0.5rem;
    --space-md: 1rem;
    --space-lg: 1.5rem;
    --space-xl: 2rem;
    
    /* Border radius */
    --radius-sm: 6px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --radius-xl: 16px;
}

/* Dark theme */
.dark {
    --bg-primary: #111827;
    --bg-secondary: #1f2937;
    --bg-tertiary: #374151;
    --text-primary: #f9fafb;
    --text-secondary: #d1d5db;
    --text-muted: #9ca3af;
    --border: #374151;
    --border-light: #4b5563;
    --shadow: rgba(0, 0, 0, 0.3);
    --shadow-lg: rgba(0, 0, 0, 0.4);
    
    --nav-bg: rgba(17, 24, 39, 0.95);
    --nav-border: rgba(55, 65, 81, 0.8);
}

/* ============================================
   BASE STYLES
   ============================================ */

* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: var(--font-family);
    line-height: 1.6;
    color: var(--text-primary);
    background: var(--bg-primary);
    margin: 0;
    padding: 0;
    transition: background-color 0.3s ease, color 0.3s ease;
}

/* ============================================
   COMPACT NAVIGATION SYSTEM - HIGH SPECIFICITY
   ============================================ */

body .modern-nav.compact,
.modern-nav {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: var(--nav-bg);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--nav-border);
    height: var(--nav-height);
    box-shadow: 0 1px 0 rgba(255, 255, 255, 0.1);
}

.modern-nav.compact {
    /* Additional styling for compact navigation */
    --nav-height: 64px;
}

.dark .modern-nav {
    box-shadow: 0 1px 0 rgba(255, 255, 255, 0.05);
}

.nav-container {
    max-width: 1440px;
    margin: 0 auto;
    padding: 0 var(--space-lg);
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 100%;
    gap: var(--space-lg);
}

/* Brand section - modern styling */
.nav-brand {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    min-width: 200px;
}

.brand-link {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    text-decoration: none;
    color: var(--text-primary);
    font-weight: 700;
    font-size: 1.25rem;
    transition: all 0.3s ease;
    padding: 4px 8px;
    border-radius: 8px;
}

.brand-link:hover {
    transform: scale(1.05);
    color: var(--primary);
}

.brand-logo {
    font-size: 1.5rem;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
    transition: transform 0.3s ease;
}

.brand-link:hover .brand-logo {
    transform: rotate(5deg);
}

.brand-text {
    color: var(--text-primary);
    background: linear-gradient(135deg, var(--text-primary), var(--primary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Integrated horizontal navigation menu */
.nav-main-menu {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 4px;
    gap: 2px;
    flex: 1;
    justify-content: center;
    max-width: 600px;
    margin: 0 var(--space-lg);
    backdrop-filter: blur(8px);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.dark .nav-main-menu {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.08);
}

.nav-main-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-xs);
    padding: 8px 16px;
    text-decoration: none;
    color: var(--text-secondary);
    font-weight: 500;
    font-size: 0.875rem;
    border-radius: 8px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    position: relative;
    flex: 1;
    min-width: 0;
    text-align: center;
}

/* Modern tab-like hover effect */
.nav-main-link:hover {
    color: var(--text-primary);
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.dark .nav-main-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
}

/* Highlighted/active link styling */
.nav-main-link.nav-highlight {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    position: relative;
}

.nav-main-link.nav-highlight::before {
    content: '';
    position: absolute;
    top: -1px;
    left: -1px;
    right: -1px;
    bottom: -1px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    border-radius: 9px;
    z-index: -1;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.nav-main-link.nav-highlight:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
}

.nav-main-link.nav-highlight:hover::before {
    opacity: 1;
}

/* Modern dropdown menu */
.nav-dropdown {
    position: relative;
    margin-left: var(--space-md);
}

.nav-dropdown-toggle {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    color: var(--text-secondary);
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(8px);
}

.dark .nav-dropdown-toggle {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.08);
}

.nav-dropdown-toggle:hover {
    color: var(--text-primary);
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.dark .nav-dropdown-toggle:hover {
    background: rgba(255, 255, 255, 0.1);
}

.nav-dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    min-width: 280px;
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px) scale(0.95);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1001;
    backdrop-filter: blur(12px);
    padding: 8px;
}

.dark .nav-dropdown-menu {
    background: rgba(17, 24, 39, 0.95);
    border-color: var(--border);
}

.nav-dropdown:hover .nav-dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

.dropdown-link {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    padding: 10px 12px;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.875rem;
    border-radius: 6px;
    transition: all 0.2s ease;
    margin-bottom: 2px;
}

.dropdown-link:hover {
    color: var(--text-primary);
    background: rgba(102, 126, 234, 0.1);
    transform: translateX(4px);
}

.dropdown-divider {
    height: 1px;
    background: var(--border);
    margin: 8px 4px;
    border-radius: 1px;
}

/* Right side actions - integrated styling */
.nav-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.nav-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-xs);
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

/* Login button - modern gradient */
.login-btn {
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: white;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.login-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.login-btn:hover::before {
    left: 100%;
}

/* Logout button - subtle design */
.logout-btn {
    background: rgba(248, 113, 113, 0.1);
    color: var(--error);
    border: 1px solid rgba(248, 113, 113, 0.2);
}

.logout-btn:hover {
    background: rgba(248, 113, 113, 0.2);
    border-color: var(--error);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(248, 113, 113, 0.3);
}

/* Theme toggle - circular design */
.theme-toggle {
    background: rgba(255, 255, 255, 0.08);
    color: var(--text-secondary);
    border: 1px solid rgba(255, 255, 255, 0.1);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    padding: 0;
    backdrop-filter: blur(8px);
    position: relative;
    overflow: hidden;
}

.dark .theme-toggle {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(255, 255, 255, 0.08);
}

.theme-toggle:hover {
    color: var(--text-primary);
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px) rotate(180deg);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.dark .theme-toggle:hover {
    background: rgba(255, 255, 255, 0.1);
}

.theme-icon {
    transition: transform 0.3s ease;
    font-size: 1.1rem;
}

/* Mobile menu toggle - hidden on desktop */
.nav-toggle {
    display: none;
    flex-direction: column;
    gap: 3px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: var(--space-sm);
}

.hamburger {
    width: 20px;
    height: 2px;
    background: var(--text-primary);
    transition: all 0.3s ease;
}

/* Mobile menu - hidden by default */
.nav-menu.mobile-only {
    display: none;
}

/* ============================================
   MOBILE RESPONSIVE
   ============================================ */

@media (max-width: 768px) {
    .nav-main-menu {
        display: none;
    }
    
    .nav-actions .nav-action-btn:not(.theme-toggle) {
        display: none;
    }
    
    .nav-toggle {
        display: flex;
    }
    
    .nav-menu.mobile-only {
        display: block;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--bg-primary);
        border: 1px solid var(--border);
        border-top: none;
        box-shadow: 0 4px 6px var(--shadow);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
    }
    
    .nav-menu.mobile-only.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .nav-mobile-link {
        display: block;
        padding: var(--space-md) var(--space-lg);
        color: var(--text-secondary);
        text-decoration: none;
        border-bottom: 1px solid var(--border-light);
        transition: all 0.2s ease;
    }
    
    .nav-mobile-link:hover {
        color: var(--primary);
        background: rgba(102, 126, 234, 0.05);
    }
    
    .nav-divider {
        height: 1px;
        background: var(--border);
        margin: var(--space-sm) 0;
    }
}

/* ============================================
   MAIN CONTENT AREA
   ============================================ */

.main-content {
    min-height: calc(100vh - var(--nav-height) - 120px);
    padding: var(--space-xl) var(--space-md);
    max-width: 1280px;
    margin: 0 auto;
}

/* ============================================
   MODERN COMPONENTS
   ============================================ */

/* Cards */
.modern-card {
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    box-shadow: 0 4px 6px var(--shadow);
    transition: all 0.3s ease;
}

.modern-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px var(--shadow-lg);
}

/* Buttons */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: var(--space-sm);
    padding: var(--space-sm) var(--space-lg);
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border: none;
    border-radius: var(--radius-md);
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-modern:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
}

/* Grid system */
.modern-grid {
    display: grid;
    gap: var(--space-lg);
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}

/* ============================================
   FOOTER
   ============================================ */

.modern-footer {
    background: var(--bg-secondary);
    border-top: 1px solid var(--border);
    padding: var(--space-xl) var(--space-md);
    margin-top: var(--space-xl);
    text-align: center;
    color: var(--text-secondary);
}

/* ============================================
   UTILITIES
   ============================================ */

.text-center { text-align: center; }
.text-muted { color: var(--text-muted); }
.mt-0 { margin-top: 0; }
.mb-0 { margin-bottom: 0; }
.hidden { display: none; }

/* Loading spinner */
.loading-spinner {
    width: 20px;
    height: 20px;
    border: 2px solid var(--border);
    border-top: 2px solid var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* ============================================
   ANIMATIONS & TRANSITIONS
   ============================================ */

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Smooth transitions for theme switching */
* {
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

/* ============================================
   ACCESSIBILITY & FOCUS STATES
   ============================================ */

.nav-main-link:focus,
.nav-action-btn:focus,
.nav-dropdown-toggle:focus {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>
<?php
    }
}
?>
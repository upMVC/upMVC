<?php
/*
 *   Created on October 13, 2025
 *   Modern CSS Styles for upMVC NoFramework
 *   Copyright (c) 2023-2025 BitsHost
 */

namespace Common\Assets;

class ModernCss
{
    public function modernStyles()
    {
?>
<style>
/* ===== MODERN UPMVC NOFRAMEWORK STYLES ===== */

:root {
    /* Modern Color Palette */
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --accent-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    
    /* Neutral Colors */
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;
    --bg-tertiary: #f1f5f9;
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --text-accent: #3b82f6;
    
    /* Dark Mode Colors */
    --dark-bg-primary: #0f172a;
    --dark-bg-secondary: #1e293b;
    --dark-bg-tertiary: #334155;
    --dark-text-primary: #f1f5f9;
    --dark-text-secondary: #cbd5e1;
    
    /* Spacing & Layout */
    --container-max-width: 1200px;
    --border-radius: 12px;
    --border-radius-lg: 16px;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    
    /* Transitions */
    --transition-fast: 0.15s ease;
    --transition-normal: 0.3s ease;
    --transition-slow: 0.5s ease;
}

/* ===== BASE STYLES ===== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

.modern-theme {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', roboto, oxygen, ubuntu, cantarell, sans-serif;
    line-height: 1.6;
    color: var(--text-primary);
    background: var(--bg-primary);
}

.modern-theme.dark {
    color: var(--dark-text-primary);
    background: var(--dark-bg-primary);
}

/* ===== MODERN NAVIGATION ===== */

.modern-nav {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    position: sticky;
    top: 0;
    z-index: 100;
    transition: var(--transition-fast);
}

.dark .modern-nav {
    background: rgba(15, 23, 42, 0.95);
    border-bottom-color: rgba(51, 65, 85, 0.8);
}

.nav-container {
    max-width: var(--container-max-width);
    margin: 0 auto;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 70px;
}

.nav-brand {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.brand-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: var(--text-primary);
    font-weight: 700;
    font-size: 1.25rem;
    transition: var(--transition-fast);
}

.dark .brand-link {
    color: var(--dark-text-primary);
}

.brand-logo {
    font-size: 1.5rem;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.brand-text {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.nav-toggle {
    display: none;
    flex-direction: column;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: var(--border-radius);
    transition: var(--transition-fast);
}

.nav-toggle:hover {
    background: var(--bg-tertiary);
}

.dark .nav-toggle:hover {
    background: var(--dark-bg-tertiary);
}

.hamburger {
    width: 22px;
    height: 2px;
    background: var(--text-primary);
    margin: 3px 0;
    transition: var(--transition-fast);
    border-radius: 2px;
}

.dark .hamburger {
    background: var(--dark-text-primary);
}

.nav-toggle.active .hamburger:nth-child(1) {
    transform: rotate(45deg) translate(5px, 5px);
}

.nav-toggle.active .hamburger:nth-child(2) {
    opacity: 0;
}

.nav-toggle.active .hamburger:nth-child(3) {
    transform: rotate(-45deg) translate(7px, -6px);
}

.nav-menu {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
    flex-wrap: wrap;
}

.nav-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-width: 160px;
}

.nav-section-title {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-secondary);
    margin-bottom: 0.25rem;
    padding: 0 0.75rem;
}

.dark .nav-section-title {
    color: var(--dark-text-secondary);
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    text-decoration: none;
    color: var(--text-primary);
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: var(--border-radius);
    transition: var(--transition-fast);
    position: relative;
    overflow: hidden;
}

.dark .nav-link {
    color: var(--dark-text-secondary);
}

.nav-link:hover {
    background: var(--bg-tertiary);
    color: var(--text-accent);
    transform: translateY(-1px);
}

.dark .nav-link:hover {
    background: var(--dark-bg-tertiary);
    color: var(--dark-text-primary);
}

.nav-icon {
    font-size: 1rem;
    min-width: 1.25rem;
}

.logout-link {
    background: var(--accent-gradient);
    color: white !important;
}

.logout-link:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* ===== MOBILE NAVIGATION ===== */

@media (max-width: 1024px) {
    .nav-toggle {
        display: flex;
    }
    
    .nav-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-top: 1px solid rgba(226, 232, 240, 0.8);
        padding: 1.5rem;
        transform: translateY(-10px);
        opacity: 0;
        visibility: hidden;
        transition: all var(--transition-normal);
        box-shadow: var(--shadow-lg);
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .dark .nav-menu {
        background: rgba(15, 23, 42, 0.98);
        border-top-color: rgba(51, 65, 85, 0.8);
    }
    
    .nav-menu.active {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }
    
    .nav-section {
        min-width: 100%;
        margin-bottom: 1.5rem;
    }
}

/* ===== DEMO BANNER ===== */

.demo-banner {
    background: var(--warning-gradient);
    color: white;
    padding: 0.75rem 0;
    text-align: center;
    font-size: 0.875rem;
    font-weight: 500;
    box-shadow: var(--shadow-sm);
}

.demo-content {
    max-width: var(--container-max-width);
    margin: 0 auto;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.demo-icon {
    font-size: 1.25rem;
}

.demo-version {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* ===== MAIN CONTENT ===== */

.modern-body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.main-content {
    flex: 1;
    padding: 2rem 0;
}

.content-container {
    max-width: var(--container-max-width);
    margin: 0 auto;
    padding: 0 1rem;
}

.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.title-accent {
    font-size: 2rem;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.page-controls {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.theme-toggle {
    background: var(--bg-secondary);
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: var(--border-radius);
    padding: 0.5rem;
    cursor: pointer;
    font-size: 1.25rem;
    transition: var(--transition-fast);
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    min-height: 2.5rem;
}

.dark .theme-toggle {
    background: var(--dark-bg-secondary);
    border-color: rgba(51, 65, 85, 0.8);
}

.theme-toggle:hover {
    background: var(--bg-tertiary);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.dark .theme-toggle:hover {
    background: var(--dark-bg-tertiary);
}

.page-content {
    background: var(--bg-secondary);
    border-radius: var(--border-radius-lg);
    padding: 2rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.dark .page-content {
    background: var(--dark-bg-secondary);
    border-color: rgba(51, 65, 85, 0.8);
}

/* ===== MODERN FOOTER ===== */

.modern-footer {
    background: var(--bg-secondary);
    border-top: 1px solid rgba(226, 232, 240, 0.8);
    margin-top: auto;
    padding: 3rem 0 1rem;
}

.dark .modern-footer {
    background: var(--dark-bg-secondary);
    border-top-color: rgba(51, 65, 85, 0.8);
}

.footer-container {
    max-width: var(--container-max-width);
    margin: 0 auto;
    padding: 0 1rem;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.footer-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.footer-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.dark .footer-title {
    color: var(--dark-text-primary);
}

.footer-description {
    color: var(--text-secondary);
    line-height: 1.7;
}

.dark .footer-description {
    color: var(--dark-text-secondary);
}

.footer-stats {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--bg-primary);
    border-radius: var(--border-radius);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary);
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.dark .stat-item {
    background: var(--dark-bg-primary);
    color: var(--dark-text-primary);
    border-color: rgba(51, 65, 85, 0.8);
}

.stat-icon {
    font-size: 1rem;
}

.footer-links {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.footer-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-secondary);
    text-decoration: none;
    transition: var(--transition-fast);
    padding: 0.5rem 0;
}

.dark .footer-link {
    color: var(--dark-text-secondary);
}

.footer-link:hover {
    color: var(--text-accent);
    transform: translateX(4px);
}

.dark .footer-link:hover {
    color: var(--dark-text-primary);
}

.link-icon {
    font-size: 1rem;
    min-width: 1.25rem;
}

.footer-quote {
    font-style: italic;
    color: var(--text-secondary);
    padding: 1rem;
    background: var(--bg-primary);
    border-radius: var(--border-radius);
    border-left: 4px solid transparent;
    border-image: var(--accent-gradient) 1;
}

.dark .footer-quote {
    color: var(--dark-text-secondary);
    background: var(--dark-bg-primary);
}

.footer-philosophy {
    color: var(--text-secondary);
    font-size: 0.875rem;
    line-height: 1.6;
}

.dark .footer-philosophy {
    color: var(--dark-text-secondary);
}

.footer-bottom {
    border-top: 1px solid rgba(226, 232, 240, 0.8);
    padding-top: 1.5rem;
}

.dark .footer-bottom {
    border-top-color: rgba(51, 65, 85, 0.8);
}

.footer-bottom-content {
    max-width: var(--container-max-width);
    margin: 0 auto;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.copyright {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.dark .copyright {
    color: var(--dark-text-secondary);
}

.version-info {
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-family: 'JetBrains Mono', monospace;
}

.dark .version-info {
    color: var(--dark-text-secondary);
}

.footer-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.badge {
    padding: 0.25rem 0.75rem;
    background: var(--primary-gradient);
    color: white;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 9999px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* ===== RESPONSIVE DESIGN ===== */

@media (max-width: 768px) {
    .page-title {
        font-size: 2rem;
    }
    
    .page-content {
        padding: 1.5rem;
    }
    
    .footer-container {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .footer-bottom-content {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
    
    .demo-content {
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* ===== UTILITY CLASSES ===== */

.text-gradient {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.shadow-glow {
    box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

/* ===== ENHANCED INTERACTIVE ELEMENTS ===== */

button, .btn {
    cursor: pointer;
    transition: var(--transition-fast);
    border: none;
    border-radius: var(--border-radius);
    font-weight: 500;
}

button:hover, .btn:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

a {
    transition: var(--transition-fast);
}

/* ===== LOADING ANIMATIONS ===== */

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(102, 126, 234, 0.1);
    border-left-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

</style>
<?php
    }
}
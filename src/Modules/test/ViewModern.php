<?php
/*
 *   Created on October 13, 2025
 *   Modern Test View using BaseViewModern
 *   Copyright (c) 2023-2025 BitsHost
 */

namespace App\Modules\Test;

use App\Common\Bmvc\BaseViewModern;

/**
 * Modern Test View - Showcasing upMVC NoFramework capabilities
 */
class ViewModern extends BaseViewModern
{
    /**
     * Modern Test View with enhanced interactivity
     */
    public function View($request, $users)
    {
        // Set custom globals for this view
        $this->addGlobal('settings', [
            'site_name' => 'upMVC Demo - Test Module',
            'theme' => 'modern-gradient'
        ]);

        $title = "🧪 Interactive Test Module";
        $this->startHead($title);
?>
        <!-- Enhanced JavaScript Libraries -->
        <script src="https://cdn.jsdelivr.net/npm/axios@1.6.0/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        
        <!-- Custom Test Module Styles -->
        <style>
            .test-container {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 2rem;
                margin-bottom: 2rem;
            }
            
            .test-card {
                background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(248,250,252,0.9) 100%);
                border-radius: 16px;
                padding: 2rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
                border: 1px solid rgba(226, 232, 240, 0.8);
                transition: all 0.3s ease;
                position: relative;
                overflow: hidden;
            }
            
            .dark .test-card {
                background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(51, 65, 85, 0.9) 100%);
                border-color: rgba(51, 65, 85, 0.8);
            }
            
            .test-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            }
            
            .test-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            
            .card-header {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                margin-bottom: 1.5rem;
            }
            
            .card-icon {
                font-size: 2rem;
                filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
            }
            
            .card-title {
                font-size: 1.25rem;
                font-weight: 600;
                color: var(--text-primary);
                margin: 0;
            }
            
            .dark .card-title {
                color: var(--dark-text-primary);
            }
            
            .user-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            
            .user-item {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1rem;
                background: rgba(255, 255, 255, 0.7);
                border-radius: 12px;
                margin-bottom: 0.75rem;
                cursor: pointer;
                transition: all 0.3s ease;
                border: 1px solid rgba(226, 232, 240, 0.5);
            }
            
            .dark .user-item {
                background: rgba(15, 23, 42, 0.7);
                border-color: rgba(51, 65, 85, 0.5);
            }
            
            .user-item:hover {
                background: rgba(102, 126, 234, 0.1);
                transform: translateX(4px);
                border-color: rgba(102, 126, 234, 0.3);
            }
            
            .user-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 600;
                font-size: 1.1rem;
            }
            
            .user-info {
                flex: 1;
            }
            
            .user-name {
                font-weight: 600;
                color: var(--text-primary);
                margin: 0 0 0.25rem 0;
            }
            
            .dark .user-name {
                color: var(--dark-text-primary);
            }
            
            .user-email {
                color: var(--text-secondary);
                font-size: 0.875rem;
                margin: 0;
            }
            
            .dark .user-email {
                color: var(--dark-text-secondary);
            }
            
            .action-button {
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                color: white;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 8px;
                font-weight: 500;
                font-size: 0.875rem;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .action-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(240, 147, 251, 0.4);
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 1rem;
                margin-top: 1.5rem;
            }
            
            .stat-item {
                text-align: center;
                padding: 1rem;
                background: rgba(255, 255, 255, 0.7);
                border-radius: 12px;
                border: 1px solid rgba(226, 232, 240, 0.5);
            }
            
            .dark .stat-item {
                background: rgba(15, 23, 42, 0.7);
                border-color: rgba(51, 65, 85, 0.5);
            }
            
            .stat-value {
                font-size: 1.5rem;
                font-weight: 700;
                color: var(--text-primary);
                margin: 0;
            }
            
            .dark .stat-value {
                color: var(--dark-text-primary);
            }
            
            .stat-label {
                font-size: 0.75rem;
                color: var(--text-secondary);
                text-transform: uppercase;
                letter-spacing: 0.05em;
                margin: 0.25rem 0 0 0;
            }
            
            .dark .stat-label {
                color: var(--dark-text-secondary);
            }
            
            .request-info {
                background: linear-gradient(135deg, rgba(67, 233, 123, 0.1) 0%, rgba(56, 249, 215, 0.1) 100%);
                border: 1px solid rgba(67, 233, 123, 0.3);
                border-radius: 12px;
                padding: 1.5rem;
                margin-top: 1.5rem;
                font-family: 'JetBrains Mono', monospace;
            }
            
            .request-title {
                font-size: 1rem;
                font-weight: 600;
                color: var(--text-primary);
                margin: 0 0 1rem 0;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .dark .request-title {
                color: var(--dark-text-primary);
            }
            
            .request-data {
                background: rgba(0, 0, 0, 0.05);
                border-radius: 8px;
                padding: 1rem;
                font-size: 0.875rem;
                color: var(--text-secondary);
                overflow-x: auto;
            }
            
            .dark .request-data {
                background: rgba(255, 255, 255, 0.05);
                color: var(--dark-text-secondary);
            }
            
            .hidden {
                opacity: 0;
                transform: translateY(20px);
                transition: all 0.5s ease;
            }
            
            .visible {
                opacity: 1;
                transform: translateY(0);
            }
            
            @media (max-width: 768px) {
                .test-container {
                    grid-template-columns: 1fr;
                    gap: 1.5rem;
                }
                
                .user-item {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 0.75rem;
                }
                
                .stats-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
        </style>
<?php
        $this->endHead();
        $this->startBody($title);
        
        $userCount = count($users);
        $visibleUsers = array_filter($users, fn($user) => isset($user->email));
        $hiddenCount = 0;
?>

        <div class="test-container">
            <!-- Users Display Card -->
            <div class="test-card" x-data="{ hiddenCount: 0, showAll: true }">
                <div class="card-header">
                    <span class="card-icon">👥</span>
                    <h3 class="card-title">Interactive User List</h3>
                </div>
                
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-style: italic;">
                    ✨ <strong>Click</strong> on any user to hide them, <strong>double-click</strong> the list area to show all users again
                </p>
                
                <ul class="user-list" id="userList" @dblclick="showAll = true; hiddenCount = 0; $refs.userList.querySelectorAll('.user-item').forEach(item => { item.classList.remove('hidden'); item.classList.add('visible'); })">
                    <?php foreach ($users as $index => $user): ?>
                    <li class="user-item visible" 
                        @click="$el.classList.toggle('hidden'); $el.classList.toggle('visible'); hiddenCount = $refs.userList.querySelectorAll('.hidden').length">
                        <div class="user-avatar">
                            <?= strtoupper(substr($user->name ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="user-info">
                            <h4 class="user-name"><?= htmlspecialchars($user->name ?? 'Unknown User') ?></h4>
                            <p class="user-email"><?= htmlspecialchars($user->email ?? 'no-email@example.com') ?></p>
                        </div>
                        <button class="action-button" @click.stop="alert('User: <?= addslashes($user->name ?? 'Unknown') ?>')">
                            View
                        </button>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <p class="stat-value"><?= $userCount ?></p>
                        <p class="stat-label">Total Users</p>
                    </div>
                    <div class="stat-item">
                        <p class="stat-value" x-text="<?= $userCount ?> - hiddenCount"><?= $userCount ?></p>
                        <p class="stat-label">Visible</p>
                    </div>
                    <div class="stat-item">
                        <p class="stat-value" x-text="hiddenCount">0</p>
                        <p class="stat-label">Hidden</p>
                    </div>
                </div>
            </div>
            
            <!-- API Demo Card -->
            <div class="test-card" x-data="{ loading: false, apiData: null }">
                <div class="card-header">
                    <span class="card-icon">🔌</span>
                    <h3 class="card-title">API Integration Demo</h3>
                </div>
                
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Demonstrate modern API integration with loading states and error handling.
                </p>
                
                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                    <button class="action-button" 
                            @click="loading = true; 
                                    setTimeout(() => { 
                                        apiData = { status: 'success', message: 'Mock API call completed!', timestamp: new Date().toLocaleTimeString() }; 
                                        loading = false; 
                                    }, 1500)">
                        📡 Fetch Data
                    </button>
                    
                    <button class="action-button" 
                            style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);"
                            @click="apiData = null">
                        🗑️ Clear
                    </button>
                </div>
                
                <div x-show="loading" style="text-align: center; padding: 2rem;">
                    <div class="loading-spinner" style="margin: 0 auto;"></div>
                    <p style="margin-top: 1rem; color: var(--text-secondary);">Loading data...</p>
                </div>
                
                <div x-show="apiData && !loading" x-transition style="background: rgba(67, 233, 123, 0.1); border: 1px solid rgba(67, 233, 123, 0.3); border-radius: 12px; padding: 1.5rem;">
                    <h4 style="color: var(--text-primary); margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
                        ✅ API Response
                    </h4>
                    <pre style="background: rgba(0,0,0,0.05); padding: 1rem; border-radius: 8px; font-size: 0.875rem; margin: 0; overflow-x: auto;" x-text="JSON.stringify(apiData, null, 2)"></pre>
                </div>
            </div>
            
            <!-- Modern Features Card -->
            <div class="test-card">
                <div class="card-header">
                    <span class="card-icon">⚡</span>
                    <h3 class="card-title">upMVC NoFramework Features</h3>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: rgba(102, 126, 234, 0.1); border-radius: 12px;">
                        <span style="font-size: 1.5rem;">🗂️</span>
                        <div>
                            <h4 style="margin: 0; color: var(--text-primary);">Modular Architecture</h4>
                            <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">True modularity without constraints</p>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: rgba(240, 147, 251, 0.1); border-radius: 12px;">
                        <span style="font-size: 1.5rem;">🏝️</span>
                        <div>
                            <h4 style="margin: 0; color: var(--text-primary);">PHP Islands</h4>
                            <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">Seamless frontend integration</p>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: rgba(67, 233, 123, 0.1); border-radius: 12px;">
                        <span style="font-size: 1.5rem;">🚀</span>
                        <div>
                            <h4 style="margin: 0; color: var(--text-primary);">Developer Freedom</h4>
                            <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">No forced conventions or bloat</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Request Information -->
        <div class="request-info">
            <h3 class="request-title">
                <span>🔍</span>
                Request Debug Information
            </h3>
            <div class="request-data">
                <strong>Request URI:</strong> <?= htmlspecialchars($request) ?><br><br>
                <strong>GET Parameters:</strong><br>
                <?php if (!empty($_GET)): ?>
                    <?= htmlspecialchars(json_encode($_GET, JSON_PRETTY_PRINT)) ?>
                <?php else: ?>
                    No GET parameters
                <?php endif; ?>
            </div>
        </div>

        <!-- Modern JavaScript Enhancement -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth animations to cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            // Observe all test cards
            document.querySelectorAll('.test-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.6s ease';
                observer.observe(card);
            });
            
            // Add ripple effect to buttons
            document.querySelectorAll('.action-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255, 255, 255, 0.3);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s ease-out;
                        pointer-events: none;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => ripple.remove(), 600);
                });
            });
        });
        
        // Add ripple animation keyframes
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
        </script>

<?php
        $this->endBody();
        $this->startFooter();
        $this->endFooter();
    }

    /**
     * Modern not logged in view
     */
    public function notLoggedIn()
    {
        $title = "🔐 Authentication Required";
        $this->startHead($title);
        ?>
        <style>
            .auth-container {
                max-width: 400px;
                margin: 2rem auto;
                text-align: center;
            }
            
            .auth-card {
                background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(248,250,252,0.9) 100%);
                border-radius: 16px;
                padding: 3rem 2rem;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
                border: 1px solid rgba(226, 232, 240, 0.8);
            }
            
            .dark .auth-card {
                background: linear-gradient(135deg, rgba(30, 41, 59, 0.9) 0%, rgba(51, 65, 85, 0.9) 100%);
                border-color: rgba(51, 65, 85, 0.8);
            }
            
            .auth-icon {
                font-size: 4rem;
                margin-bottom: 1.5rem;
                filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
            }
            
            .auth-message {
                color: var(--text-secondary);
                font-size: 1.125rem;
                line-height: 1.6;
                margin-bottom: 2rem;
            }
            
            .dark .auth-message {
                color: var(--dark-text-secondary);
            }
            
            .auth-button {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 0.75rem 2rem;
                border-radius: 12px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                transition: all 0.3s ease;
                text-decoration: none;
                display: inline-block;
            }
            
            .auth-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
            }
        </style>
        <?php
        $this->endHead();
        $this->startBody($title);
        ?>
        
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-icon">🔐</div>
                <p class="auth-message">
                    You need to be authenticated to access this test module. 
                    Please log in to continue exploring upMVC NoFramework features.
                </p>
                <a href="<?php echo BASE_URL; ?>/auth" class="auth-button">
                    🔑 Go to Login
                </a>
            </div>
        </div>
        
        <?php
        $this->endBody();
        $this->startFooter();
        $this->endFooter();
    }
}











/**
 * Enhanced App\Modules\TestParent Module JavaScript
 * Auto-discovery enabled, environment-aware
 */

class EnhancedApp\Modules\TestParentModule {
    constructor() {
        this.moduleName = 'App\Modules\TestParent';
        this.version = '2.0-enhanced';
        this.debugMode = this.isDebugMode();
        this.apiEndpoint = this.getApiEndpoint();
        
        this.log('Enhanced module initialized');
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initializeComponents();
        this.checkAutoDiscovery();
        
        // Enhanced: Add fade-in animation
        document.querySelectorAll('.card').forEach(card => {
            card.classList.add(`${this.moduleName}-fade-in`);
        });
    }
    
    bindEvents() {
        // Enhanced event binding with error handling
        try {
            this.bindFormEvents();
            this.bindNavigationEvents();
            this.bindApiEvents();
        } catch (error) {
            this.logError('Event binding failed:', error);
        }
    }
    
    bindFormEvents() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        });
    }
    
    bindNavigationEvents() {
        // Enhanced navigation with loading states
        const navLinks = document.querySelectorAll('a[href*="{${this.moduleName.toLowerCase()}}"]');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => this.handleNavigation(e));
        });
    }
    
    bindApiEvents() {
        const apiButtons = document.querySelectorAll('[data-api-action]');
        apiButtons.forEach(button => {
            button.addEventListener('click', (e) => this.handleApiAction(e));
        });
    }
    
    handleFormSubmit(event) {
        const form = event.target;
        const submitButton = form.querySelector('button[type="submit"]');
        
        if (submitButton) {
            this.setLoading(submitButton, true);
            
            // Auto-restore loading state after 3 seconds (fallback)
            setTimeout(() => {
                this.setLoading(submitButton, false);
            }, 3000);
        }
        
        this.log('Form submitted:', form.action);
        return true;
    }
    
    handleNavigation(event) {
        const link = event.target.closest('a');
        this.log('Navigation:', link.href);
        
        // Add loading indicator for navigation
        this.showLoadingIndicator();
    }
    
    handleApiAction(event) {
        event.preventDefault();
        const button = event.target.closest('[data-api-action]');
        const action = button.dataset.apiAction;
        
        this.log('API Action:', action);
        this.callApi(action, button);
    }
    
    async callApi(action, button = null) {
        if (button) this.setLoading(button, true);
        
        try {
            const response = await fetch(this.apiEndpoint);
            const data = await response.json();
            
            this.log('API Response:', data);
            this.showApiResponse(data);
            
        } catch (error) {
            this.logError('API Error:', error);
            this.showError('API request failed');
        } finally {
            if (button) this.setLoading(button, false);
        }
    }
    
    showApiResponse(data) {
        const alert = this.createAlert('API Response', JSON.stringify(data, null, 2), 'info');
        this.showAlert(alert);
    }
    
    initializeComponents() {
        this.initializeTooltips();
        this.initializeModals();
        this.checkEnhancedFeatures();
    }
    
    initializeTooltips() {
        if (typeof bootstrap !== 'undefined') {
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(tooltip => {
                new bootstrap.Tooltip(tooltip);
            });
        }
    }
    
    initializeModals() {
        if (typeof bootstrap !== 'undefined') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                new bootstrap.Modal(modal);
            });
        }
    }
    
    checkAutoDiscovery() {
        this.log('Auto-discovery check: Module loaded via InitModsImproved.php');
        
        if (this.debugMode) {
            this.showDebugInfo();
        }
    }
    
    checkEnhancedFeatures() {
        const features = {
            autoDiscovery: true,
            environmentAware: this.getEnvironment() !== 'unknown',
            cachingEnabled: this.isCachingEnabled(),
            debugMode: this.debugMode
        };
        
        this.log('Enhanced features:', features);
        return features;
    }
    
    showDebugInfo() {
        const debugPanel = document.createElement('div');
        debugPanel.className = 'debug-info mt-3';
        debugPanel.innerHTML = \`
            <h6><i class="fas fa-bug"></i> Debug Information</h6>
            <p><strong>Module:</strong> ${this.moduleName} v${this.version}</p>
            <p><strong>Environment:</strong> ${this.getEnvironment()}</p>
            <p><strong>Auto-Discovery:</strong> ✅ Enabled</p>
            <p><strong>Caching:</strong> ${this.isCachingEnabled() ? '✅ Enabled' : '❌ Disabled'}</p>
            <button class="btn btn-sm btn-outline-primary" onclick="window.enhanced${this.moduleName}.testApi()">
                <i class="fas fa-code"></i> Test API
            </button>
        \`;
        
        const container = document.querySelector('.container');
        if (container) {
            container.appendChild(debugPanel);
        }
    }
    
    testApi() {
        this.log('Testing API endpoint...');
        this.callApi('test');
    }
    
    // Utility methods
    setLoading(element, isLoading) {
        if (isLoading) {
            element.classList.add(\`${this.moduleName}-loading\`);
            element.disabled = true;
            element.dataset.originalText = element.innerHTML;
            element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        } else {
            element.classList.remove(\`${this.moduleName}-loading\`);
            element.disabled = false;
            element.innerHTML = element.dataset.originalText || element.innerHTML;
        }
    }
    
    showLoadingIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
        indicator.style.backgroundColor = 'rgba(0,0,0,0.5)';
        indicator.style.zIndex = '9999';
        indicator.innerHTML = '<div class="spinner-border text-light" role="status"></div>';
        
        document.body.appendChild(indicator);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            indicator.remove();
        }, 5000);
    }
    
    createAlert(title, message, type = 'info') {
        return \`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <h6 class="alert-heading">${title}</h6>
                <pre class="mb-0" style="white-space: pre-wrap;">${message}</pre>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        \`;
    }
    
    showAlert(html) {
        const container = document.querySelector('.container');
        if (container) {
            const alertDiv = document.createElement('div');
            alertDiv.innerHTML = html;
            container.insertBefore(alertDiv.firstElementChild, container.firstChild);
        }
    }
    
    showError(message) {
        const alert = this.createAlert('Error', message, 'danger');
        this.showAlert(alert);
    }
    
    // Environment detection
    isDebugMode() {
        return document.querySelector('.debug-indicator') !== null;
    }
    
    getEnvironment() {
        const debugIndicator = document.querySelector('.debug-indicator');
        if (debugIndicator) {
            return debugIndicator.textContent.includes('development') ? 'development' : 'production';
        }
        return 'unknown';
    }
    
    isCachingEnabled() {
        // This would need to be set by the PHP template
        return true; // Default assumption
    }
    
    getApiEndpoint() {
        const baseUrl = window.BASE_URL || '';
        return \`${baseUrl}/{${this.moduleName.toLowerCase()}}/api\`;
    }
    
    // Logging methods
    log(...args) {
        if (this.debugMode) {
            console.log(\`[${this.moduleName}]\`, ...args);
        }
    }
    
    logError(...args) {
        console.error(\`[${this.moduleName}] ERROR:\`, ...args);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.enhancedApp\Modules\TestParent = new EnhancedApp\Modules\TestParentModule();
});

// Export for external use
window.EnhancedApp\Modules\TestParentModule = EnhancedApp\Modules\TestParentModule;
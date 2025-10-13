<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   Enhanced Module Example - View
 */

namespace Enhanced;

use Common\Bmvc\BaseView;

/**
 * Enhanced View
 * 
 * Example view demonstrating new upMVC features
 */
class View extends BaseView
{
    /**
     * Render the enhanced demo page
     */
    public function render(array $data): void
    {
        $this->startHead('upMVC Enhanced Features Demo');
        ?>
        <style>
            .feature-card {
                background: white;
                border-radius: 8px;
                padding: 20px;
                margin: 10px 0;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin: 20px 0;
            }
            .code-block {
                background: #f4f4f4;
                padding: 15px;
                border-radius: 5px;
                font-family: monospace;
                font-size: 14px;
                overflow-x: auto;
            }
            .success { color: #28a745; }
            .info { color: #17a2b8; }
        </style>
        <?php
        $this->endHead();
        
        $this->startBody('Enhanced upMVC Features Demo');
        ?>

        <div class="feature-card">
            <h2>🚀 Enhanced upMVC Core System</h2>
            <p>This page demonstrates the new core system enhancements:</p>
            <ul>
                <li><strong>Middleware System</strong> - Request/response processing pipeline</li>
                <li><strong>Dependency Injection</strong> - Automatic dependency resolution</li>
                <li><strong>Enhanced Error Handling</strong> - Custom exceptions and logging</li>
                <li><strong>Caching System</strong> - Multiple cache drivers with tagging</li>
                <li><strong>Event System</strong> - Publish-subscribe pattern</li>
                <li><strong>Configuration Management</strong> - Environment-based config</li>
            </ul>
        </div>

        <div class="stats-grid">
            <div class="feature-card">
                <h3>📊 Request Information</h3>
                <div class="code-block">
Route: <?= htmlspecialchars($data['route']) ?><br>
Method: <?= htmlspecialchars($data['method']) ?><br>
Timestamp: <?= date('Y-m-d H:i:s') ?>
                </div>
            </div>

            <div class="feature-card">
                <h3>💾 Cache Demo</h3>
                <p class="success">✓ Data cached successfully!</p>
                <div class="code-block">
Generated: <?= htmlspecialchars($data['cached_data']['generated_at']) ?><br>
Random: <?= htmlspecialchars($data['cached_data']['random_number']) ?><br>
Cache TTL: 5 minutes
                </div>
                <p><em>Refresh the page - the random number won't change until cache expires!</em></p>
            </div>

            <div class="feature-card">
                <h3>📡 Event System</h3>
                <p class="success">✓ UserRegistered event dispatched!</p>
                <div class="code-block">
<?php
foreach ($data['event_stats'] as $key => $value) {
    echo htmlspecialchars($key) . ': ' . htmlspecialchars(is_array($value) ? json_encode($value) : $value) . "<br>";
}
?>
                </div>
            </div>

            <div class="feature-card">
                <h3>📈 Cache Statistics</h3>
                <div class="code-block">
<?php
if (is_array($data['cache_stats'])) {
    foreach ($data['cache_stats'] as $key => $value) {
        echo htmlspecialchars($key) . ': ' . htmlspecialchars(is_array($value) ? json_encode($value) : $value) . "<br>";
    }
} else {
    echo htmlspecialchars($data['cache_stats']);
}
?>
                </div>
            </div>
        </div>

        <div class="feature-card">
            <h3>🔧 Features Demonstrated</h3>
            <ul>
                <?php foreach ($data['cached_data']['features_demonstrated'] as $feature): ?>
                    <li class="info"><?= htmlspecialchars($feature) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="feature-card">
            <h3>🔗 API Endpoints</h3>
            <p>Try these enhanced API endpoints:</p>
            <ul>
                <li><a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/enhanced/api" target="_blank">Enhanced API Demo</a></li>
                <li><a href="<?= defined('BASE_URL') ? BASE_URL : '' ?>/enhanced" target="_blank">Refresh This Page</a></li>
            </ul>
        </div>

        <div class="feature-card">
            <h3>📖 Learn More</h3>
            <p>Check out the <code>ENHANCEMENTS.md</code> file for detailed documentation on all new features.</p>
        </div>

        <?php
        $this->endBody();
        $this->startFooter();
        $this->endFooter();
    }
}
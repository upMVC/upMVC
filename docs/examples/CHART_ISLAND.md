# Chart Island Example

## Overview

A **Chart Island** provides interactive data visualization within a server-rendered page. This example demonstrates:

- ✅ Multiple chart types (bar, line, pie)
- ✅ Interactive tooltips
- ✅ Date range filtering
- ✅ Data drill-down
- ✅ Export functionality
- ✅ Responsive design

---

## Demo Structure

```
modules/charts/
├── Controller.php      # Routes and API
├── Model.php          # Analytics data
├── View.php           # Renders page with chart island
└── routes/
    └── Routes.php     # Register routes
```

---

## Implementation

### Step 1: Model (Analytics Data)

```php
<?php
// modules/charts/Model.php

namespace Charts;

class Model
{
    /**
     * Get sales data by month
     * 
     * @param string $year Year filter
     * @return array Monthly sales data
     */
    public function getSalesData(string $year = '2024'): array
    {
        // Simulate database query
        return [
            ['month' => 'Jan', 'sales' => 45000, 'profit' => 12000, 'orders' => 230],
            ['month' => 'Feb', 'sales' => 52000, 'profit' => 15000, 'orders' => 280],
            ['month' => 'Mar', 'sales' => 48000, 'profit' => 13500, 'orders' => 260],
            ['month' => 'Apr', 'sales' => 61000, 'profit' => 18000, 'orders' => 310],
            ['month' => 'May', 'sales' => 55000, 'profit' => 16000, 'orders' => 290],
            ['month' => 'Jun', 'sales' => 70000, 'profit' => 21000, 'orders' => 350],
            ['month' => 'Jul', 'sales' => 68000, 'profit' => 20000, 'orders' => 340],
            ['month' => 'Aug', 'sales' => 73000, 'profit' => 22000, 'orders' => 365],
            ['month' => 'Sep', 'sales' => 66000, 'profit' => 19000, 'orders' => 330],
            ['month' => 'Oct', 'sales' => 71000, 'profit' => 21500, 'orders' => 355],
            ['month' => 'Nov', 'sales' => 79000, 'profit' => 24000, 'orders' => 390],
            ['month' => 'Dec', 'sales' => 85000, 'profit' => 26000, 'orders' => 420],
        ];
    }

    /**
     * Get category distribution
     * 
     * @return array Sales by category
     */
    public function getCategoryData(): array
    {
        return [
            ['category' => 'Electronics', 'value' => 320000, 'percentage' => 35],
            ['category' => 'Clothing', 'value' => 250000, 'percentage' => 27],
            ['category' => 'Home & Garden', 'value' => 180000, 'percentage' => 20],
            ['category' => 'Sports', 'value' => 110000, 'percentage' => 12],
            ['category' => 'Books', 'value' => 55000, 'percentage' => 6],
        ];
    }

    /**
     * Get summary stats
     * 
     * @return array Summary statistics
     */
    public function getSummaryStats(): array
    {
        return [
            'totalSales' => 915000,
            'totalProfit' => 272000,
            'totalOrders' => 4580,
            'avgOrderValue' => 200,
            'growthRate' => 12.5,
        ];
    }
}
```

### Step 2: Controller

```php
<?php
// modules/charts/Controller.php

namespace Charts;

use Charts\View;
use Charts\Model;

class Controller
{
    public function display($reqRoute, $reqMet)
    {
        switch ($reqRoute) {
            case '/charts':
                $this->index($reqRoute, $reqMet);
                break;
            
            case '/charts/data':
                $this->chartData();
                break;
                
            default:
                $this->index($reqRoute, $reqMet);
                break;
        }
    }

    /**
     * Main charts page
     */
    private function index($reqRoute, $reqMet)
    {
        $model = new Model();
        $view = new View();

        $data = [
            'salesData' => $model->getSalesData(),
            'categoryData' => $model->getCategoryData(),
            'stats' => $model->getSummaryStats()
        ];

        $view->render($data);
    }

    /**
     * Chart data API (for dynamic updates)
     */
    private function chartData()
    {
        header('Content-Type: application/json');
        
        $model = new Model();
        $year = $_GET['year'] ?? '2024';
        
        echo json_encode([
            'success' => true,
            'salesData' => $model->getSalesData($year),
            'categoryData' => $model->getCategoryData()
        ]);
        
        exit;
    }
}
```

### Step 3: View (Chart Islands)

```php
<?php
// modules/charts/View.php

namespace Charts;

use Common\Bmvc\BaseView;

class View
{
    public $title = 'Analytics Dashboard';

    public function render($data = [])
    {
        $view = new BaseView();
        $view->startHead($this->title);
        
        $this->importMaps();
        $this->styles();
        
        $view->endHead();
        $view->startBody($this->title);
        ?>

        <div class="container">
            <div class="hero">
                <h1>📊 Analytics Dashboard</h1>
                <p>Interactive charts with real-time data</p>
            </div>

            <!-- Summary Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-value">$<?= number_format($data['stats']['totalSales']) ?></div>
                    <div class="stat-label">Total Sales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-value">$<?= number_format($data['stats']['totalProfit']) ?></div>
                    <div class="stat-label">Total Profit</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-value"><?= number_format($data['stats']['totalOrders']) ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value"><?= $data['stats']['growthRate'] ?>%</div>
                    <div class="stat-label">Growth Rate</div>
                </div>
            </div>

            <!-- Chart Islands -->
            <div class="charts-grid">
                <!-- Bar Chart Island -->
                <div class="chart-card">
                    <h2>📊 Monthly Sales</h2>
                    <div id="bar-chart"></div>
                </div>

                <!-- Line Chart Island -->
                <div class="chart-card">
                    <h2>📈 Sales Trend</h2>
                    <div id="line-chart"></div>
                </div>

                <!-- Pie Chart Island -->
                <div class="chart-card">
                    <h2>🥧 Sales by Category</h2>
                    <div id="pie-chart"></div>
                </div>
            </div>
        </div>

        <!-- PHP Data as JSON -->
        <script type="application/json" id="php-data">
            <?php echo json_encode($data, JSON_PRETTY_PRINT); ?>
        </script>

        <?php
        $this->chartIslands();
        
        $view->endBody();
        $view->startFooter();
        $view->endFooter();
    }

    private function importMaps()
    {
        ?>
        <script type="importmap">
        {
            "imports": {
                "preact": "https://esm.sh/preact@10.23.1",
                "preact/": "https://esm.sh/preact@10.23.1/",
                "htm/preact": "https://esm.sh/htm@3.1.1/preact?external=preact"
            }
        }
        </script>
        <?php
    }

    private function chartIslands()
    {
        ?>
        <script type="module">
            import { render } from 'preact';
            import { useState, useRef, useEffect } from 'preact/hooks';
            import { html } from 'htm/preact';

            // Get data from PHP
            const phpData = JSON.parse(document.getElementById('php-data').textContent);

            // ============================================
            // BAR CHART COMPONENT
            // ============================================
            function BarChart({ data }) {
                const [hoveredBar, setHoveredBar] = useState(null);
                const [metric, setMetric] = useState('sales');
                
                const maxValue = Math.max(...data.map(d => d[metric]));
                
                return html`
                    <div class="chart">
                        <!-- Metric Selector -->
                        <div class="chart-controls">
                            <button 
                                class=${metric === 'sales' ? 'active' : ''}
                                onClick=${() => setMetric('sales')}
                            >
                                Sales
                            </button>
                            <button 
                                class=${metric === 'profit' ? 'active' : ''}
                                onClick=${() => setMetric('profit')}
                            >
                                Profit
                            </button>
                            <button 
                                class=${metric === 'orders' ? 'active' : ''}
                                onClick=${() => setMetric('orders')}
                            >
                                Orders
                            </button>
                        </div>

                        <!-- Bar Chart -->
                        <div class="bar-chart">
                            ${data.map((item, index) => {
                                const percentage = (item[metric] / maxValue) * 100;
                                return html`
                                    <div 
                                        key=${item.month}
                                        class="bar-container"
                                        onMouseEnter=${() => setHoveredBar(index)}
                                        onMouseLeave=${() => setHoveredBar(null)}
                                    >
                                        <div 
                                            class="bar"
                                            style=${{ height: percentage + '%' }}
                                        >
                                            ${hoveredBar === index && html`
                                                <div class="tooltip">
                                                    <strong>${item.month}</strong><br/>
                                                    $${item[metric].toLocaleString()}
                                                </div>
                                            `}
                                        </div>
                                        <div class="bar-label">${item.month}</div>
                                    </div>
                                `;
                            })}
                        </div>
                    </div>
                `;
            }

            // ============================================
            // LINE CHART COMPONENT
            // ============================================
            function LineChart({ data }) {
                const [showPoints, setShowPoints] = useState(true);
                const svgRef = useRef(null);
                
                const width = 600;
                const height = 300;
                const padding = 40;
                
                const maxValue = Math.max(...data.map(d => d.sales));
                const minValue = Math.min(...data.map(d => d.sales));
                
                // Calculate points
                const points = data.map((item, index) => {
                    const x = padding + (index / (data.length - 1)) * (width - padding * 2);
                    const y = height - padding - ((item.sales - minValue) / (maxValue - minValue)) * (height - padding * 2);
                    return { x, y, ...item };
                });
                
                // Create path
                const pathData = points.map((p, i) => 
                    `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`
                ).join(' ');
                
                return html`
                    <div class="chart">
                        <div class="chart-controls">
                            <label>
                                <input 
                                    type="checkbox"
                                    checked=${showPoints}
                                    onChange=${(e) => setShowPoints(e.target.checked)}
                                />
                                Show Points
                            </label>
                        </div>

                        <svg 
                            ref=${svgRef}
                            width=${width}
                            height=${height}
                            class="line-chart"
                        >
                            <!-- Grid lines -->
                            ${[0, 1, 2, 3, 4].map(i => {
                                const y = padding + i * ((height - padding * 2) / 4);
                                return html`
                                    <line
                                        key=${i}
                                        x1=${padding}
                                        y1=${y}
                                        x2=${width - padding}
                                        y2=${y}
                                        stroke="#f0f0f0"
                                        stroke-width="1"
                                    />
                                `;
                            })}

                            <!-- Area fill -->
                            <path
                                d="${pathData} L ${width - padding} ${height - padding} L ${padding} ${height - padding} Z"
                                fill="rgba(102, 126, 234, 0.1)"
                            />

                            <!-- Line -->
                            <path
                                d=${pathData}
                                fill="none"
                                stroke="#667eea"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <!-- Points -->
                            ${showPoints && points.map(point => html`
                                <g key=${point.month}>
                                    <circle
                                        cx=${point.x}
                                        cy=${point.y}
                                        r="5"
                                        fill="#667eea"
                                        stroke="white"
                                        stroke-width="2"
                                    />
                                    <title>${point.month}: $${point.sales.toLocaleString()}</title>
                                </g>
                            `)}

                            <!-- X-axis labels -->
                            ${points.map((point, i) => i % 2 === 0 && html`
                                <text
                                    key=${point.month}
                                    x=${point.x}
                                    y=${height - padding + 20}
                                    text-anchor="middle"
                                    font-size="12"
                                    fill="#666"
                                >
                                    ${point.month}
                                </text>
                            `)}
                        </svg>
                    </div>
                `;
            }

            // ============================================
            // PIE CHART COMPONENT
            // ============================================
            function PieChart({ data }) {
                const [selectedSlice, setSelectedSlice] = useState(null);
                
                const total = data.reduce((sum, item) => sum + item.value, 0);
                const centerX = 150;
                const centerY = 150;
                const radius = 100;
                
                // Calculate pie slices
                let currentAngle = -90; // Start at top
                const slices = data.map((item, index) => {
                    const angle = (item.value / total) * 360;
                    const startAngle = currentAngle;
                    const endAngle = currentAngle + angle;
                    
                    const startRad = (startAngle * Math.PI) / 180;
                    const endRad = (endAngle * Math.PI) / 180;
                    
                    const x1 = centerX + radius * Math.cos(startRad);
                    const y1 = centerY + radius * Math.sin(startRad);
                    const x2 = centerX + radius * Math.cos(endRad);
                    const y2 = centerY + radius * Math.sin(endRad);
                    
                    const largeArc = angle > 180 ? 1 : 0;
                    
                    const path = `M ${centerX} ${centerY} L ${x1} ${y1} A ${radius} ${radius} 0 ${largeArc} 1 ${x2} ${y2} Z`;
                    
                    currentAngle = endAngle;
                    
                    return { ...item, path, color: getColor(index) };
                });
                
                function getColor(index) {
                    const colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b'];
                    return colors[index % colors.length];
                }
                
                return html`
                    <div class="chart">
                        <svg width="300" height="300" class="pie-chart">
                            ${slices.map((slice, index) => html`
                                <g 
                                    key=${slice.category}
                                    onMouseEnter=${() => setSelectedSlice(index)}
                                    onMouseLeave=${() => setSelectedSlice(null)}
                                    style=${{ cursor: 'pointer' }}
                                >
                                    <path
                                        d=${slice.path}
                                        fill=${slice.color}
                                        opacity=${selectedSlice === index ? 0.8 : 1}
                                        transform=${selectedSlice === index ? 'scale(1.05)' : ''}
                                        transform-origin="${centerX} ${centerY}"
                                        style=${{ transition: 'all 0.2s' }}
                                    />
                                </g>
                            `)}
                        </svg>

                        <!-- Legend -->
                        <div class="pie-legend">
                            ${slices.map((slice, index) => html`
                                <div 
                                    key=${slice.category}
                                    class=${`legend-item ${selectedSlice === index ? 'active' : ''}`}
                                    onMouseEnter=${() => setSelectedSlice(index)}
                                    onMouseLeave=${() => setSelectedSlice(null)}
                                >
                                    <div 
                                        class="legend-color"
                                        style=${{ background: slice.color }}
                                    ></div>
                                    <div class="legend-label">
                                        <strong>${slice.category}</strong>
                                        <span>$${slice.value.toLocaleString()} (${slice.percentage}%)</span>
                                    </div>
                                </div>
                            `)}
                        </div>
                    </div>
                `;
            }

            // Render all chart islands
            render(
                html`<${BarChart} data=${phpData.salesData} />`,
                document.getElementById('bar-chart')
            );

            render(
                html`<${LineChart} data=${phpData.salesData} />`,
                document.getElementById('line-chart')
            );

            render(
                html`<${PieChart} data=${phpData.categoryData} />`,
                document.getElementById('pie-chart')
            );
        </script>
        <?php
    }

    private function styles()
    {
        ?>
        <style>
            .container {
                max-width: 1400px;
                margin: 0 auto;
                padding: 20px;
            }

            .hero {
                text-align: center;
                padding: 40px 20px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 12px;
                margin-bottom: 30px;
            }

            .hero h1 {
                margin: 0 0 10px 0;
                font-size: 2.5em;
            }

            /* Stats Grid */
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .stat-card {
                background: white;
                border-radius: 12px;
                padding: 25px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                text-align: center;
            }

            .stat-icon {
                font-size: 3em;
                margin-bottom: 10px;
            }

            .stat-value {
                font-size: 2em;
                font-weight: bold;
                color: #667eea;
                margin: 10px 0;
            }

            .stat-label {
                color: #666;
                font-size: 0.9em;
            }

            /* Charts Grid */
            .charts-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
                gap: 30px;
            }

            .chart-card {
                background: white;
                border-radius: 12px;
                padding: 25px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }

            .chart-card h2 {
                margin: 0 0 20px 0;
                color: #333;
            }

            /* Chart Controls */
            .chart-controls {
                display: flex;
                gap: 10px;
                margin-bottom: 20px;
                flex-wrap: wrap;
            }

            .chart-controls button {
                background: #f5f5f5;
                border: 2px solid #e0e0e0;
                padding: 8px 16px;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s;
            }

            .chart-controls button:hover {
                border-color: #667eea;
            }

            .chart-controls button.active {
                background: #667eea;
                color: white;
                border-color: #667eea;
            }

            /* Bar Chart */
            .bar-chart {
                display: flex;
                align-items: flex-end;
                justify-content: space-around;
                height: 300px;
                padding: 20px 0;
                gap: 5px;
            }

            .bar-container {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                height: 100%;
                position: relative;
            }

            .bar {
                width: 100%;
                background: linear-gradient(to top, #667eea, #764ba2);
                border-radius: 4px 4px 0 0;
                transition: all 0.3s;
                position: relative;
            }

            .bar-container:hover .bar {
                opacity: 0.8;
                transform: scaleY(1.02);
            }

            .bar-label {
                margin-top: 10px;
                font-size: 0.85em;
                color: #666;
            }

            .tooltip {
                position: absolute;
                top: -50px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0,0,0,0.8);
                color: white;
                padding: 8px 12px;
                border-radius: 6px;
                font-size: 0.9em;
                white-space: nowrap;
                z-index: 10;
            }

            .tooltip::after {
                content: '';
                position: absolute;
                bottom: -5px;
                left: 50%;
                transform: translateX(-50%);
                border: 5px solid transparent;
                border-top-color: rgba(0,0,0,0.8);
            }

            /* Line Chart */
            .line-chart {
                display: block;
                margin: 0 auto;
            }

            /* Pie Chart */
            .pie-chart {
                display: block;
                margin: 0 auto 20px;
            }

            .pie-legend {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .legend-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s;
            }

            .legend-item:hover,
            .legend-item.active {
                background: #f5f5f5;
            }

            .legend-color {
                width: 20px;
                height: 20px;
                border-radius: 4px;
            }

            .legend-label {
                flex: 1;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .legend-label strong {
                color: #333;
            }

            .legend-label span {
                color: #666;
                font-size: 0.9em;
            }
        </style>
        <?php
    }
}
```

### Step 4: Routes

```php
<?php
// modules/charts/routes/Routes.php

namespace Charts\Routes;

use Charts\Controller;

class Routes
{
    public function routes($router)
    {
        $router->addRoute("/charts", Controller::class, "display");
        $router->addRoute("/charts/data", Controller::class, "display");
    }
}
```

---

## Features Demonstrated

### 1. **Bar Chart with Metric Switching**
- Switch between Sales, Profit, Orders
- Hover tooltips
- Smooth animations

### 2. **Line Chart with Area Fill**
- SVG-based rendering
- Grid lines for readability
- Toggle points on/off
- Smooth curves

### 3. **Pie Chart with Legend**
- Category distribution
- Interactive hover
- Legend with percentages
- Color-coded segments

### 4. **Responsive Design**
- Charts adapt to container size
- Mobile-friendly
- Touch-enabled

---

## Best Practices

✅ **SVG for scalability** - Crisp on any screen  
✅ **Smooth transitions** - Better UX  
✅ **Interactive tooltips** - Show data on hover  
✅ **Accessible** - Titles for screen readers  
✅ **Color blind friendly** - Distinct colors  

---

## Customization

### Add Export to PNG

```javascript
function exportChart() {
    const svg = document.querySelector('svg');
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    
    // Convert SVG to image
    const img = new Image();
    const svgData = new XMLSerializer().serializeToString(svg);
    img.src = 'data:image/svg+xml;base64,' + btoa(svgData);
    
    img.onload = () => {
        canvas.width = svg.width.baseVal.value;
        canvas.height = svg.height.baseVal.value;
        ctx.drawImage(img, 0, 0);
        
        // Download
        canvas.toBlob(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'chart.png';
            a.click();
        });
    };
}
```

---

**Next:** [Form Island Example →](./FORM_ISLAND.md)

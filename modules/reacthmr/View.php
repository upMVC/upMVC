<?php

/*
 *   Created on October 17, 2025
 *   
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 */

namespace Reacthmr;

use Common\Bmvc\BaseView;

/**
 * ReactHMR View
 * 
 * Demonstrates Hot Module Reload with ES Modules
 * Uses Import Maps, HTM, and SSE for instant browser refresh
 */
class View
{
    public $title = 'React HMR - Hot Module Reload with ES Modules (No Build!)';

    public function render($data = [])
    {
        $view = new BaseView();
        $view->startHead($this->title);
        
        // Import Maps for ES modules
        $this->importMaps();
        
        // HMR Client Script
        $this->hmrClient();
        
        // Custom styles
        $this->styles();
        
        $view->endHead();
        $view->startBody($this->title);
        ?>

        <div class="container">
            <!-- HMR Status Indicator -->
            <div id="hmr-status" class="hmr-status">
                <span class="status-dot"></span>
                <span class="status-text">HMR Connected</span>
            </div>

            <div class="hero">
                <h1>🔥 Hot Module Reload</h1>
                <p class="subtitle">Edit components, save file, watch browser auto-reload</p>
                <p class="tech-stack">Preact + HTM + SSE + PHP = Zero Build HMR</p>
            </div>

            <!-- Demo Sections -->
            <div class="demo-grid">
                <!-- Preact Counter -->
                <div class="demo-card">
                    <h2>⚡ Preact Counter</h2>
                    <p>Try editing <code>components/Counter.js</code></p>
                    <div id="counter-app"></div>
                </div>

                <!-- User Table -->
                <div class="demo-card">
                    <h2>👥 User Table (PHP Data)</h2>
                    <p>Data from PHP, rendered by Preact</p>
                    <div id="users-app"></div>
                </div>

                <!-- Todo App -->
                <div class="demo-card full-width">
                    <h2>✅ Todo App</h2>
                    <p>Component from separate file: <code>components/TodoApp.js</code></p>
                    <div id="todo-app"></div>
                </div>

                <!-- Stats Dashboard -->
                <div class="demo-card">
                    <h2>📊 Stats Dashboard</h2>
                    <div id="stats-app"></div>
                </div>

                <!-- Vue Example -->
                <div class="demo-card">
                    <h2>💚 Vue.js Example</h2>
                    <p>HMR works with any framework!</p>
                    <div id="vue-app"></div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="instructions">
                <h2>🚀 Try It Yourself</h2>
                <ol>
                    <li>Open <code>modules/reacthmr/View.php</code> in your editor</li>
                    <li>Change some text or component code below</li>
                    <li>Save the file now</li>
                    <li>Watch this page auto-reload! ✨</li>
                </ol>
                
                <div class="code-block">
                    <strong>Watched Files:</strong>
                    <ul>
                        <li>modules/reacthmr/View.php</li>
                        <li>modules/reacthmr/Controller.php</li>
                        <li>modules/reacthmr/components/*.js</li>
                        <li>modules/reacthmr/templates/*.html</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- PHP Data as JSON -->
        <script type="application/json" id="php-data">
            <?php echo json_encode($data, JSON_PRETTY_PRINT); ?>
        </script>

        <?php
        // Load Preact components
        $this->preactCounter();
        $this->preactUsers();
        $this->preactTodoApp();
        $this->preactStats();
        $this->vueExample();
        
        $view->endBody();
        $view->startFooter();
        $view->endFooter();
    }

    /**
     * Import Maps for ES modules
     */
    private function importMaps()
    {
        ?>
        <script type="importmap">
        {
            "imports": {
                "preact": "https://esm.sh/preact@10.23.1",
                "preact/": "https://esm.sh/preact@10.23.1/",
                "htm/preact": "https://esm.sh/htm@3.1.1/preact?external=preact",
                "@preact/signals": "https://esm.sh/@preact/signals@1.3.0?external=preact"
            }
        }
        </script>
        <script src="https://unpkg.com/vue@3.4.21/dist/vue.global.js"></script>
        <?php
    }

    /**
     * HMR Client - Connects to SSE stream and reloads on changes
     */
    private function hmrClient()
    {
        ?>
        <script>
        // HMR Client using Server-Sent Events
        (function() {
            const statusEl = document.createElement('div');
            statusEl.id = 'hmr-status';
            statusEl.className = 'hmr-status';
            
            const updateStatus = (text, className = '') => {
                const statusText = statusEl.querySelector('.status-text');
                const statusDot = statusEl.querySelector('.status-dot');
                if (statusText) statusText.textContent = text;
                if (statusDot) {
                    statusDot.className = 'status-dot ' + className;
                }
            };

            let eventSource;
            let reconnectTimer;

            const connect = () => {
                console.log('[HMR] Connecting...');
                
                eventSource = new EventSource('<?php echo BASE_URL; ?>/reacthmr/hmr');
                
                eventSource.onopen = () => {
                    console.log('[HMR] Connected');
                    updateStatus('HMR Connected', 'connected');
                };

                eventSource.addEventListener('reload', (e) => {
                    const data = JSON.parse(e.data);
                    console.log('[HMR] Reload event received:', data.message);
                    updateStatus('Reloading...', 'reloading');
                    
                    // Smooth reload with fade effect
                    document.body.style.opacity = '0.5';
                    setTimeout(() => {
                        location.reload();
                    }, 300);
                });

                eventSource.onerror = (e) => {
                    console.log('[HMR] Connection lost, reconnecting...');
                    updateStatus('HMR Reconnecting...', 'disconnected');
                    eventSource.close();
                    
                    // Reconnect after 2 seconds
                    clearTimeout(reconnectTimer);
                    reconnectTimer = setTimeout(connect, 2000);
                };
            };

            // Start HMR connection
            if (typeof EventSource !== 'undefined') {
                connect();
            } else {
                console.warn('[HMR] SSE not supported by browser');
                updateStatus('HMR Not Supported', 'error');
            }

            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                if (eventSource) {
                    eventSource.close();
                }
            });
        })();
        </script>
        <?php
    }

    /**
     * Custom styles
     */
    private function styles()
    {
        ?>
        <style>
            .container {
                max-width: 1200px;
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

            .subtitle {
                font-size: 1.2em;
                opacity: 0.9;
                margin: 10px 0;
            }

            .tech-stack {
                font-family: 'Courier New', monospace;
                background: rgba(0,0,0,0.2);
                padding: 8px 16px;
                border-radius: 6px;
                display: inline-block;
                margin-top: 10px;
            }

            .demo-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .demo-card {
                background: white;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                padding: 20px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }

            .demo-card.full-width {
                grid-column: 1 / -1;
            }

            .demo-card h2 {
                margin-top: 0;
                color: #333;
                font-size: 1.3em;
            }

            .demo-card p {
                color: #666;
                font-size: 0.9em;
            }

            .demo-card code {
                background: #f5f5f5;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 0.85em;
                color: #d63384;
            }

            /* HMR Status Indicator */
            .hmr-status {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: white;
                padding: 10px 16px;
                border-radius: 20px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 0.9em;
                z-index: 1000;
            }

            .status-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #4caf50;
                animation: pulse 2s infinite;
            }

            .status-dot.connected {
                background: #4caf50;
            }

            .status-dot.disconnected {
                background: #ff9800;
                animation: pulse-fast 1s infinite;
            }

            .status-dot.reloading {
                background: #2196f3;
            }

            .status-dot.error {
                background: #f44336;
            }

            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }

            @keyframes pulse-fast {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.3; }
            }

            .instructions {
                background: #f8f9fa;
                padding: 30px;
                border-radius: 8px;
                border-left: 4px solid #667eea;
            }

            .instructions h2 {
                margin-top: 0;
                color: #333;
            }

            .instructions ol {
                line-height: 1.8;
                color: #555;
            }

            .code-block {
                background: #2d2d2d;
                color: #f8f8f2;
                padding: 15px;
                border-radius: 6px;
                margin-top: 15px;
            }

            .code-block ul {
                margin: 10px 0;
                padding-left: 20px;
            }

            .code-block li {
                font-family: 'Courier New', monospace;
                font-size: 0.9em;
                color: #a6e22e;
            }

            /* Component styles */
            .counter {
                display: flex;
                gap: 10px;
                align-items: center;
                justify-content: center;
                margin: 20px 0;
            }

            .counter button {
                background: #667eea;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 1em;
                transition: all 0.2s;
            }

            .counter button:hover {
                background: #5568d3;
                transform: translateY(-1px);
            }

            .counter input {
                width: 80px;
                text-align: center;
                font-size: 1.5em;
                font-weight: bold;
                border: 2px solid #e0e0e0;
                border-radius: 6px;
                padding: 5px;
            }

            .user-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }

            .user-table th {
                background: #f5f5f5;
                padding: 10px;
                text-align: left;
                border-bottom: 2px solid #e0e0e0;
            }

            .user-table td {
                padding: 10px;
                border-bottom: 1px solid #f0f0f0;
            }

            .user-table tr:hover {
                background: #f9f9f9;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                margin-top: 10px;
            }

            .stat-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                border-radius: 8px;
                text-align: center;
            }

            .stat-card .number {
                font-size: 2em;
                font-weight: bold;
                margin: 10px 0;
            }

            .stat-card .label {
                opacity: 0.9;
                font-size: 0.9em;
            }

            .todo-list {
                margin-top: 15px;
            }

            .todo-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 12px;
                background: #f8f9fa;
                margin-bottom: 8px;
                border-radius: 6px;
                transition: all 0.2s;
            }

            .todo-item:hover {
                background: #e9ecef;
            }

            .todo-item.completed {
                opacity: 0.6;
            }

            .todo-item.completed .todo-text {
                text-decoration: line-through;
            }

            .todo-input {
                display: flex;
                gap: 10px;
                margin-bottom: 15px;
            }

            .todo-input input {
                flex: 1;
                padding: 10px;
                border: 2px solid #e0e0e0;
                border-radius: 6px;
                font-size: 1em;
            }

            .todo-input button {
                background: #28a745;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 1em;
            }

            .todo-input button:hover {
                background: #218838;
            }
        </style>
        <?php
    }

    /**
     * Preact Counter Component
     */
    private function preactCounter()
    {
        ?>
        <script type="module">
            import { render } from 'preact';
            import { useState } from 'preact/hooks';
            import { html } from 'htm/preact';

            function Counter() {
                const [count, setCount] = useState(0);

                return html`
                    <div class="counter">
                        <button onClick=${() => setCount(count - 1)}>➖ Decrement</button>
                        <input readonly value=${count} />
                        <button onClick=${() => setCount(count + 1)}>➕ Increment</button>
                    </div>
                    <p style="text-align: center; color: #666;">
                        You clicked ${count} time${count !== 1 ? 's' : ''}
                    </p>
                `;
            }

            render(html`<${Counter} />`, document.getElementById('counter-app'));
        </script>
        <?php
    }

    /**
     * Preact Users Table Component (using PHP data)
     */
    private function preactUsers()
    {
        ?>
        <script type="module">
            import { render } from 'preact';
            import { html } from 'htm/preact';

            const phpData = JSON.parse(document.getElementById('php-data').textContent);
            const users = phpData.users || [];

            function UserTable() {
                return html`
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${users.map(user => html`
                                <tr key=${user.id}>
                                    <td>${user.id}</td>
                                    <td>${user.name}</td>
                                    <td>${user.email}</td>
                                    <td><span style="background: #667eea; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em;">${user.role}</span></td>
                                </tr>
                            `)}
                        </tbody>
                    </table>
                `;
            }

            render(html`<${UserTable} />`, document.getElementById('users-app'));
        </script>
        <?php
    }

    /**
     * Preact TodoApp Component (from external file)
     */
    private function preactTodoApp()
    {
        ?>
        <script type="module">
            import { render } from 'preact';
            import { useState } from 'preact/hooks';
            import { html } from 'htm/preact';

            function TodoApp({ initialTodos = [] }) {
                const defaultTodos = initialTodos.length > 0 ? initialTodos : [
                    { id: 1, text: 'Learn Preact with HMR', completed: true },
                    { id: 2, text: 'Build amazing apps', completed: false },
                    { id: 3, text: 'No webpack needed!', completed: false }
                ];
                
                const [todos, setTodos] = useState(defaultTodos);
                const [inputValue, setInputValue] = useState('');

                const addTodo = (e) => {
                    e.preventDefault();
                    if (inputValue.trim()) {
                        const newTodo = {
                            id: Date.now(),
                            text: inputValue,
                            completed: false
                        };
                        setTodos([...todos, newTodo]);
                        setInputValue('');
                    }
                };

                const toggleTodo = (id) => {
                    setTodos(todos.map(todo => 
                        todo.id === id ? { ...todo, completed: !todo.completed } : todo
                    ));
                };

                const deleteTodo = (id) => {
                    setTodos(todos.filter(todo => todo.id !== id));
                };

                return html`
                    <div>
                        <form class="todo-input" onSubmit=${addTodo}>
                            <input 
                                type="text"
                                value=${inputValue}
                                onInput=${(e) => setInputValue(e.target.value)}
                                placeholder="Add a new todo..."
                            />
                            <button type="submit">Add Todo</button>
                        </form>

                        <div class="todo-list">
                            ${todos.map(todo => html`
                                <div key=${todo.id} class=${`todo-item ${todo.completed ? 'completed' : ''}`}>
                                    <input 
                                        type="checkbox"
                                        checked=${todo.completed}
                                        onChange=${() => toggleTodo(todo.id)}
                                    />
                                    <span class="todo-text" style="flex: 1;">${todo.text}</span>
                                    <button 
                                        onClick=${() => deleteTodo(todo.id)}
                                        style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer;"
                                    >
                                        Delete
                                    </button>
                                </div>
                            `)}
                        </div>

                        <p style="margin-top: 15px; color: #666; text-align: center;">
                            ${todos.filter(t => !t.completed).length} pending, 
                            ${todos.filter(t => t.completed).length} completed
                        </p>
                    </div>
                `;
            }

            const phpData = JSON.parse(document.getElementById('php-data').textContent);
            const initialTodos = phpData.todos || [];

            render(html`<${TodoApp} initialTodos=${initialTodos} />`, document.getElementById('todo-app'));
        </script>
        <?php
    }

    /**
     * Preact Stats Component (using PHP data)
     */
    private function preactStats()
    {
        ?>
        <script type="module">
            import { render } from 'preact';
            import { html } from 'htm/preact';

            const phpData = JSON.parse(document.getElementById('php-data').textContent);
            const stats = phpData.stats || {};

            function Stats() {
                return html`
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="label">Total Users</div>
                            <div class="number">${stats.totalUsers || 0}</div>
                        </div>
                        <div class="stat-card">
                            <div class="label">Active Projects</div>
                            <div class="number">${stats.activeProjects || 0}</div>
                        </div>
                        <div class="stat-card">
                            <div class="label">Completed Tasks</div>
                            <div class="number">${stats.completedTasks || 0}</div>
                        </div>
                        <div class="stat-card">
                            <div class="label">Pending Tasks</div>
                            <div class="number">${stats.pendingTasks || 0}</div>
                        </div>
                    </div>
                `;
            }

            render(html`<${Stats} />`, document.getElementById('stats-app'));
        </script>
        <?php
    }

    /**
     * Vue.js Example (to show HMR works with any framework)
     */
    private function vueExample()
    {
        ?>
        <script>
            const { createApp } = Vue;

            createApp({
                data() {
                    return {
                        message: 'Hello from Vue.js!',
                        counter: 0
                    }
                },
                template: `
                    <div style="text-align: center;">
                        <h3 style="color: #42b883;">{{ message }}</h3>
                        <button @click="counter++" style="background: #42b883; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-size: 1em;">
                            Clicked {{ counter }} times
                        </button>
                    </div>
                `
            }).mount('#vue-app');
        </script>
        <?php
    }
}

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Overview Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Overview</h3>
            <i class="fas fa-chart-line text-blue-500 text-xl"></i>
        </div>
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Total Users</span>
                <span class="text-2xl font-semibold dark:text-white">250</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Active Users</span>
                <span class="text-2xl font-semibold dark:text-white">180</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">New Today</span>
                <span class="text-2xl font-semibold dark:text-white">12</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Quick Actions</h3>
            <i class="fas fa-bolt text-yellow-500 text-xl"></i>
        </div>
        <div class="space-y-4">
            <a href="<?php echo BASE_URL ?>/dashboardexample/users/add" class="flex items-center text-blue-600 hover:text-blue-700">
                <i class="fas fa-user-plus mr-2"></i>
                Add New User
            </a>
            <a href="<?php echo BASE_URL ?>/dashboardexample/reports" class="flex items-center text-blue-600 hover:text-blue-700">
                <i class="fas fa-chart-bar mr-2"></i>
                Generate Report
            </a>
            <a href="<?php echo BASE_URL ?>/dashboardexample/settings" class="flex items-center text-blue-600 hover:text-blue-700">
                <i class="fas fa-cog mr-2"></i>
                System Settings
            </a>
        </div>
    </div>

    <!-- Recent Activity Card -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Activity</h3>
            <i class="fas fa-clock text-green-500 text-xl"></i>
        </div>
        <div class="space-y-4">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-user-edit text-blue-500"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">User profile updated</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">2 minutes ago</p>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-user-plus text-green-500"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">New user registered</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">1 hour ago</p>
                </div>
            </div>
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <i class="fas fa-cog text-yellow-500"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Settings updated</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">3 hours ago</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Status Section -->
<div class="mt-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">System Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-server text-green-500 text-xl mr-3"></i>
                    <div>
                        <p class="text-sm font-medium text-green-800">Server Status</p>
                        <p class="text-xl font-semibold text-green-600">Operational</p>
                    </div>
                </div>
            </div>
            
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-memory text-blue-500 text-xl mr-3"></i>
                    <div>
                        <p class="text-sm font-medium text-blue-800">Memory Usage</p>
                        <p class="text-xl font-semibold text-blue-600">65%</p>
                    </div>
                </div>
            </div>
            
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-database text-yellow-500 text-xl mr-3"></i>
                    <div>
                        <p class="text-sm font-medium text-yellow-800">Database</p>
                        <p class="text-xl font-semibold text-yellow-600">Connected</p>
                    </div>
                </div>
            </div>
            
            <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-clock text-purple-500 text-xl mr-3"></i>
                    <div>
                        <p class="text-sm font-medium text-purple-800">Uptime</p>
                        <p class="text-xl font-semibold text-purple-600">99.9%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

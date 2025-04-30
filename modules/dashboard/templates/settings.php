<div class="bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Dashboard Settings</h2>
    </div>

    <?php if (isset($error)): ?>
        <div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
            <p><?= $error ?></p>
        </div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL ?>/dashboard/settings" method="POST" class="p-6 space-y-6">
        <!-- Site Name -->
        <div>
            <label for="site_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Site Name</label>
            <input type="text" name="site_name" id="site_name" 
                value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">The name of your dashboard</p>
        </div>

        <!-- Theme -->
        <div>
            <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Theme</label>
            <select name="theme" id="theme" 
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="light" <?= ($settings['theme'] ?? '') === 'light' ? 'selected' : '' ?>>Light</option>
                <option value="dark" <?= ($settings['theme'] ?? '') === 'dark' ? 'selected' : '' ?>>Dark</option>
            </select>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose the dashboard theme</p>
        </div>

        <!-- Items Per Page -->
        <div>
            <label for="items_per_page" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Items Per Page</label>
            <input type="number" name="items_per_page" id="items_per_page" min="5" max="100"
                value="<?= htmlspecialchars($settings['items_per_page'] ?? '10') ?>"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Number of items to display per page in lists</p>
        </div>

        <!-- Maintenance Mode -->
        <div>
            <label for="maintenance_mode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Maintenance Mode</label>
            <select name="maintenance_mode" id="maintenance_mode" 
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="false" <?= ($settings['maintenance_mode'] ?? '') === 'false' ? 'selected' : '' ?>>Disabled</option>
                <option value="true" <?= ($settings['maintenance_mode'] ?? '') === 'true' ? 'selected' : '' ?>>Enabled</option>
            </select>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enable maintenance mode to restrict access</p>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" 
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Save Settings
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const themeSelect = document.getElementById('theme');
    const html = document.documentElement;

    // Function to update theme
    function updateTheme(theme) {
        if (theme === 'dark') {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    }

    // Handle theme changes
    themeSelect.addEventListener('change', function() {
        updateTheme(this.value);
    });

    // Initialize theme
    updateTheme(themeSelect.value);
});
</script>

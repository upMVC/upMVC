<div class="max-w-md w-full space-y-8 bg-white p-10 rounded-lg shadow-md">
    <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Sign in to Dashboard
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
        Login with admin@example.com / admin 123</p>
    </div>
    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?= $error ?></span>
        </div>
    <?php endif; ?>
    <form class="mt-8 space-y-6" action="<?php echo BASE_URL ?>/dashboardexample/login" method="POST">
        <div class="rounded-md shadow-sm -space-y-px">
            <div>
                <label for="email" class="sr-only">Email address</label>
                <input id="email" name="email" type="email" required 
                    class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                    placeholder="Email address"
                    value="<?= $email ?? '' ?>">
            </div>
            <div>
                <label for="password" class="sr-only">Password</label>
                <input id="password" name="password" type="password" required 
                    class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                    placeholder="Password">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember-me" name="remember-me" type="checkbox" 
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                    Remember me
                </label>
            </div>

            <div class="text-sm">
                <a href="#" class="font-medium text-blue-600 hover:text-blue-500">
                    Forgot your password?
                </a>
            </div>
        </div>

        <div>
            <button type="submit" 
                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                    <i class="fas fa-lock"></i>
                </span>
                Sign in
            </button>
        </div>
    </form>
</div>

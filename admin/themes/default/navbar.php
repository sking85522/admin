<header class="bg-white shadow">
    <div class="flex items-center justify-between px-6 py-4">
        <div class="flex items-center">
            <button id="sidebarToggle" class="text-gray-500 focus:outline-none md:hidden">
                <i class="fas fa-bars fa-lg"></i>
            </button>
            <span class="ml-4 font-semibold text-gray-700 capitalize text-lg">
                <?php
                // Simple logic to show current module name
                $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $parts = explode('/', trim($uri, '/'));
                $current = end($parts);
                if ($current === 'admin' || $current === '') echo 'Dashboard';
                else echo h($current);
                ?>
            </span>
        </div>

        <div class="flex items-center">
            <div x-data="{ dropdownOpen: false }" class="relative">
                <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-2 focus:outline-none">
                    <img class="h-8 w-8 rounded-full object-cover border-2 border-gray-300" src="https://ui-avatars.com/api/?name=<?= urlencode(Session::get('username')) ?>&background=random" alt="User avatar">
                    <span class="text-sm font-medium text-gray-700 hidden sm:block"><?= h(Session::get('username')) ?></span>
                    <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                </button>

                <div x-show="dropdownOpen" @click.away="dropdownOpen = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md overflow-hidden shadow-xl z-10 hidden" :class="{'hidden': !dropdownOpen}">
                    <a href="<?= APP_URL ?>/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-cog mr-2"></i> Settings</a>
                    <div class="border-t border-gray-100"></div>
                    <a href="<?= APP_URL ?>/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // Simple toggle for mobile sidebar
    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('hidden');
        sidebar.classList.toggle('absolute');
        sidebar.classList.toggle('z-50');
    });
</script>

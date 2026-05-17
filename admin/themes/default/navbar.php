<header class="bg-white shadow">
    <div class="flex items-center justify-between px-6 py-4">
        <div class="flex items-center flex-1">
            <button id="sidebarToggle" class="text-gray-500 focus:outline-none md:hidden mr-4">
                <i class="fas fa-bars fa-lg"></i>
            </button>

            <div class="hidden sm:block flex-1 max-w-md relative" x-data="{ query: '', results: [], show: false, search() {
                if (this.query.length < 2) { this.results = []; this.show = false; return; }
                fetch('<?= APP_URL ?>/api/search.php?q=' + encodeURIComponent(this.query))
                .then(res => res.json())
                .then(data => { this.results = data.results; this.show = true; });
            }}">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-search text-gray-400"></i>
                    </span>
                    <input type="text" x-model="query" @input.debounce.300ms="search" @click.away="show = false" class="block w-full rounded-md border-0 py-1.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6 bg-gray-50 transition-colors" placeholder="Search across system...">
                </div>

                <div x-show="show" class="absolute z-50 mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200 py-1">
                    <template x-if="results.length === 0">
                        <div class="px-4 py-2 text-sm text-gray-500">No results found.</div>
                    </template>
                    <template x-for="result in results">
                        <a :href="result.url" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                            <i class="fas text-gray-400 mr-2 w-4" :class="result.icon"></i>
                            <span class="font-semibold text-xs text-gray-500 w-12" x-text="result.type"></span>
                            <span class="truncate" x-text="result.title"></span>
                        </a>
                    </template>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-4">

            <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="text-gray-500 hover:text-primary focus:outline-none">
                <i class="fas" :class="darkMode ? 'fa-sun text-yellow-400' : 'fa-moon'"></i>
            </button>

            <div x-data="{ dropdownOpen: false }" class="relative">
                <button @click="dropdownOpen = !dropdownOpen" class="flex items-center space-x-2 focus:outline-none">
                    <img class="h-8 w-8 rounded-full object-cover border-2 border-gray-300" src="https://ui-avatars.com/api/?name=<?= urlencode(Session::get('username')) ?>&background=random" alt="User avatar">
                    <span class="text-sm font-medium text-gray-700 hidden sm:block"><?= h(Session::get('username')) ?></span>
                    <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                </button>

                <div x-show="dropdownOpen" @click.away="dropdownOpen = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md overflow-hidden shadow-xl z-10 hidden" :class="{'hidden': !dropdownOpen}">
                    <a href="<?= APP_URL ?>/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-cog mr-2"></i> <?= __('Settings') ?></a>
                    <div class="border-t border-gray-100"></div>
                    <a href="<?= APP_URL ?>/logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"><i class="fas fa-sign-out-alt mr-2"></i> <?= __('Logout') ?></a>
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

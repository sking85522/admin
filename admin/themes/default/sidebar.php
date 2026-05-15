<div class="bg-gray-800 text-white w-64 flex-shrink-0 h-screen flex flex-col hidden md:flex transition-all duration-300" id="sidebar">
    <div class="flex items-center justify-center h-16 bg-gray-900 border-b border-gray-700">
        <a href="<?= APP_URL ?>" class="text-xl font-bold text-white hover:text-gray-300 transition-colors">
            <i class="fas fa-cube mr-2"></i> <?= h(APP_NAME) ?>
        </a>
    </div>

    <div class="overflow-y-auto flex-1 py-4">
        <nav>
            <ul class="space-y-1">
                <li>
                    <a href="<?= APP_URL ?>/" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors">
                        <i class="fas fa-tachometer-alt w-6"></i> Dashboard
                    </a>
                </li>

                <li class="px-6 py-2 text-xs uppercase text-gray-500 font-semibold mt-4">Modules</li>

                <?php
                // Dynamically scan modules directory
                $modules = array_diff(scandir(MODULES_PATH), ['.', '..', 'dashboard']);
                foreach ($modules as $module):
                    if (is_dir(MODULES_PATH . '/' . $module)):
                ?>
                    <li>
                        <a href="<?= APP_URL ?>/<?= $module ?>" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors capitalize">
                            <i class="fas fa-folder w-6"></i> <?= h($module) ?>
                        </a>
                    </li>
                <?php
                    endif;
                endforeach;
                ?>
            </ul>
        </nav>
    </div>

    <div class="p-4 bg-gray-900 border-t border-gray-700">
        <div class="text-sm">
            <div class="text-gray-400">Logged in as</div>
            <div class="font-bold truncate"><?= h(Session::get('username')) ?></div>
        </div>
    </div>
</div>

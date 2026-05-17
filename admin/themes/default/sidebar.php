<div class="bg-gray-800 text-white w-64 flex-shrink-0 h-screen flex flex-col hidden md:flex transition-all duration-300" id="sidebar">
    <div class="flex items-center justify-center h-16 bg-gray-900 border-b border-gray-700">
        <a href="<?= APP_URL ?>" class="text-xl font-bold text-white hover:text-gray-300 transition-colors">
            <i class="fas fa-cube mr-2"></i> <?= h(APP_NAME) ?>
        </a>
    </div>

    <div class="overflow-y-auto flex-1 py-4">
        <nav>
            <ul class="space-y-1">
                <?php if (Auth::hasPermission('view_dashboard')): ?>
                <li>
                    <a href="<?= APP_URL ?>/" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors">
                        <i class="fas fa-tachometer-alt w-6 text-gray-400"></i> <?= __('Dashboard') ?>
                    </a>
                </li>
                <?php endif; ?>

                <li class="px-6 py-2 text-xs uppercase text-gray-500 font-semibold mt-4 border-b border-gray-700 pb-2 mb-2">Content</li>
                <?php if (Auth::hasPermission('manage_posts')): ?>
                    <li><a href="<?= APP_URL ?>/posts" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"><i class="fas fa-pen w-6 text-gray-400"></i> <?= __('Posts') ?></a></li>
                <?php endif; ?>
                <?php if (Auth::hasPermission('manage_files')): ?>
                    <li><a href="<?= APP_URL ?>/filemanager" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"><i class="fas fa-image w-6 text-gray-400"></i> <?= __('Media') ?></a></li>
                <?php endif; ?>
                <?php if (Auth::hasPermission('manage_forms')): ?>
                    <li><a href="<?= APP_URL ?>/forms" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"><i class="fas fa-clipboard-list w-6 text-gray-400"></i> <?= __('Forms') ?></a></li>
                <?php endif; ?>

                <li class="px-6 py-2 text-xs uppercase text-gray-500 font-semibold mt-4 border-b border-gray-700 pb-2 mb-2">System</li>
                <?php if (Auth::hasPermission('manage_users')): ?>
                    <li><a href="<?= APP_URL ?>/users" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"><i class="fas fa-users w-6 text-gray-400"></i> <?= __('Users') ?></a></li>
                <?php endif; ?>
                <?php if (Auth::hasPermission('manage_roles')): ?>
                    <li><a href="<?= APP_URL ?>/roles" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"><i class="fas fa-user-shield w-6 text-gray-400"></i> Roles</a></li>
                <?php endif; ?>
                <?php if (Auth::hasPermission('manage_settings')): ?>
                    <li><a href="<?= APP_URL ?>/settings" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"><i class="fas fa-cog w-6 text-gray-400"></i> <?= __('Settings') ?></a></li>
                <?php endif; ?>

                <li class="px-6 py-2 text-xs uppercase text-gray-500 font-semibold mt-4 border-b border-gray-700 pb-2 mb-2">Advanced</li>
                <?php if (Auth::hasPermission('manage_plugins')): ?>
                    <li><a href="<?= APP_URL ?>/plugins" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"><i class="fas fa-plug w-6 text-gray-400"></i> Plugins</a></li>
                <?php endif; ?>
                <?php if (Auth::hasPermission('manage_backups')): ?>
                    <li><a href="<?= APP_URL ?>/backups" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"><i class="fas fa-database w-6 text-gray-400"></i> Backups</a></li>
                <?php endif; ?>
                <?php if (Auth::hasPermission('manage_settings')): ?>
                    <li><a href="<?= APP_URL ?>/updater" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"><i class="fas fa-sync-alt w-6 text-green-400"></i> System Updates</a></li>
                <?php endif; ?>
                <?php if (Auth::hasPermission('manage_apikeys')): ?>
                    <li><a href="<?= APP_URL ?>/apikeys" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"><i class="fas fa-key w-6 text-gray-400"></i> API Keys</a></li>
                <?php endif; ?>
                <?php if (Auth::hasPermission('view_audit')): ?>
                    <li><a href="<?= APP_URL ?>/audit" class="flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"><i class="fas fa-list-alt w-6 text-gray-400"></i> Audit Logs</a></li>
                <?php endif; ?>
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

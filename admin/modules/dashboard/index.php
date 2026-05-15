<?php
// admin/modules/dashboard/index.php

$usersDb = new JsonDB(USERS_PATH . '/admin.json');
$postsDb = new JsonDB(CONTENT_PATH . '/posts.json');
$pagesDb = new JsonDB(CONTENT_PATH . '/pages.json');

$userCount = count($usersDb->getAll());
$postCount = count($postsDb->getAll());
$pageCount = count($pagesDb->getAll());

?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
    <p class="text-sm text-gray-500">Welcome back, <?= h(Session::get('username')) ?>!</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-blue-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                <i class="fas fa-users fa-2x"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 truncate">Total Users</p>
                <p class="text-2xl font-semibold text-gray-900"><?= $userCount ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-green-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                <i class="fas fa-file-alt fa-2x"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 truncate">Total Posts</p>
                <p class="text-2xl font-semibold text-gray-900"><?= $postCount ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-purple-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-500 mr-4">
                <i class="fas fa-copy fa-2x"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 truncate">Total Pages</p>
                <p class="text-2xl font-semibold text-gray-900"><?= $pageCount ?></p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="<?= APP_URL ?>/posts/create" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-plus text-gray-400 mb-2"></i>
            <span class="text-sm text-gray-600">New Post</span>
        </a>
        <a href="<?= APP_URL ?>/users/create" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-user-plus text-gray-400 mb-2"></i>
            <span class="text-sm text-gray-600">New User</span>
        </a>
        <a href="<?= APP_URL ?>/settings" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-cog text-gray-400 mb-2"></i>
            <span class="text-sm text-gray-600">Settings</span>
        </a>
        <a href="<?= APP_URL ?>/generate_module.php" target="_blank" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <i class="fas fa-magic text-gray-400 mb-2"></i>
            <span class="text-sm text-gray-600">Module Generator</span>
        </a>
    </div>
</div>

<?php
// admin/modules/users/index.php

$db = new JsonDB(USERS_PATH . '/admin.json');
$users = $db->getAll();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Users</h1>
    <a href="<?= APP_URL ?>/users/create" class="bg-primary hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors">
        <i class="fas fa-plus mr-2"></i> Add User
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($users as $user): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($user['name'] ?? $user['username']) ?>&background=random" alt="">
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900"><?= h($user['name'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900"><?= h($user['username']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            <?= h(ucfirst($user['role'] ?? 'user')) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="<?= APP_URL ?>/users/edit/<?= $user['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i> Edit</a>
                        <?php if ($user['id'] != Session::get('user_id')): ?>
                        <form action="<?= APP_URL ?>/users/delete" method="POST" class="inline-block" onsubmit="return confirm('Delete this user?');">
                            <?= Csrf::getTokenField() ?>
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i> Delete</button>
                        </form>
                        <?php else: ?>
                            <span class="text-gray-400 cursor-not-allowed" title="Cannot delete yourself"><i class="fas fa-trash"></i> Delete</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
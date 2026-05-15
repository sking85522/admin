<?php
// admin/modules/posts/index.php

$db = new JsonDB(CONTENT_PATH . '/posts.json');
$posts = $db->getAll();
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Posts</h1>
    <a href="<?= APP_URL ?>/posts/create" class="bg-primary hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors">
        <i class="fas fa-plus mr-2"></i> Create Post
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($posts)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No posts found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?= h($post['title']) ?></div>
                            <div class="text-sm text-gray-500">/<?= h($post['slug']) ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $post['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                <?= h(ucfirst($post['status'])) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= h($post['created_at']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?= APP_URL ?>/posts/edit/<?= $post['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3"><i class="fas fa-edit"></i> Edit</a>
                            <form action="<?= APP_URL ?>/posts/delete" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                <?= Csrf::getTokenField() ?>
                                <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
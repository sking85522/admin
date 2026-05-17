<?php
// admin/modules/posts/edit.php

$db = new JsonDB(CONTENT_PATH . '/posts.json');
global $id; // Passed from index.php router

$post = $db->getById($id);

if (!$post) {
    Session::setFlash('error', 'Post not found.');
    redirect(APP_URL . '/posts');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $data = [
        'title' => $_POST['title'] ?? '',
        'slug' => $_POST['slug'] ?? '',
        'content' => $_POST['content'] ?? '',
        'status' => $_POST['status'] ?? 'draft',
        'category' => $_POST['category'] ?? 'Uncategorized',
        'meta_title' => $_POST['meta_title'] ?? '',
        'meta_desc' => $_POST['meta_desc'] ?? ''
    ];

    $validator = new Validator();
    if ($validator->validate($data, [
        'title' => 'required',
        'slug' => 'required',
        'content' => 'required'
    ])) {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $db->update($id, $data);

        Session::setFlash('success', 'Post updated successfully.');
        redirect(APP_URL . '/posts');
    } else {
        $errors = $validator->getErrors();
        $post = array_merge($post, $data); // Repopulate form
    }
}
?>

<div class="mb-6">
    <div class="flex items-center">
        <a href="<?= APP_URL ?>/posts" class="text-gray-500 hover:text-gray-700 mr-4"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-semibold text-gray-900">Edit Post</h1>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden p-6">
    <form action="" method="POST" class="space-y-6">
        <?= Csrf::getTokenField() ?>

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" name="title" id="title" value="<?= h($post['title']) ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
            <?php if (isset($errors['title'])): ?>
                <p class="mt-2 text-sm text-red-600"><?= h($errors['title'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
            <input type="text" name="slug" id="slug" value="<?= h($post['slug']) ?>" data-manually-edited="true" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
            <?php if (isset($errors['slug'])): ?>
                <p class="mt-2 text-sm text-red-600"><?= h($errors['slug'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                    <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                <input type="text" name="category" id="category" value="<?= h($post['category'] ?? 'Uncategorized') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
            </div>
        </div>

        <div>
            <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
            <textarea id="content" name="content" rows="10" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"><?= h($post['content']) ?></textarea>
            <?php if (isset($errors['content'])): ?>
                <p class="mt-2 text-sm text-red-600"><?= h($errors['content'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">SEO Settings</h3>
            <div class="space-y-4">
                <div>
                    <label for="meta_title" class="block text-sm font-medium text-gray-700">Meta Title</label>
                    <input type="text" name="meta_title" id="meta_title" value="<?= h($post['meta_title'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                </div>
                <div>
                    <label for="meta_desc" class="block text-sm font-medium text-gray-700">Meta Description</label>
                    <textarea name="meta_desc" id="meta_desc" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm"><?= h($post['meta_desc'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-primary hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors">
                Update Post
            </button>
        </div>
    </form>
</div>

<!-- TinyMCE Rich Text Editor -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#content',
        plugins: 'lists link image code table',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image | code',
        height: 400,
        menubar: false
    });
</script>
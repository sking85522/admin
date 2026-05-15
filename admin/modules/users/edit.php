<?php
// admin/modules/users/edit.php

$db = new JsonDB(USERS_PATH . '/admin.json');
global $id;

$user = $db->getById($id);

if (!$user) {
    Session::setFlash('error', 'User not found.');
    redirect(APP_URL . '/users');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $data = [
        'name' => $_POST['name'] ?? '',
        'username' => $_POST['username'] ?? '',
        'role' => $_POST['role'] ?? 'user',
    ];

    $validator = new Validator();
    if ($validator->validate($data, [
        'name' => 'required',
        'username' => 'required'
    ])) {

        // Update password only if provided
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 6) {
                $errors['password'][] = 'Password must be at least 6 characters.';
            } else {
                 $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
        }

        if (empty($errors)) {
            // Check for existing username (excluding self)
            $exists = false;
            foreach($db->getAll() as $u) {
                if ($u['username'] === $data['username'] && $u['id'] !== $id) {
                    $exists = true;
                    break;
                }
            }

            if ($exists) {
                $errors['username'][] = 'Username already exists.';
            } else {
                $db->update($id, $data);
                Session::setFlash('success', 'User updated successfully.');
                redirect(APP_URL . '/users');
            }
        }
    } else {
        $errors = array_merge($errors, $validator->getErrors());
    }
}
?>

<div class="mb-6">
    <div class="flex items-center">
        <a href="<?= APP_URL ?>/users" class="text-gray-500 hover:text-gray-700 mr-4"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-semibold text-gray-900">Edit User</h1>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden p-6 max-w-2xl">
    <form action="" method="POST" class="space-y-6">
        <?= Csrf::getTokenField() ?>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" name="name" id="name" value="<?= h($user['name'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
            <?php if (isset($errors['name'])): ?>
                <p class="mt-2 text-sm text-red-600"><?= h($errors['name'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" name="username" id="username" value="<?= h($user['username']) ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
            <?php if (isset($errors['username'])): ?>
                <p class="mt-2 text-sm text-red-600"><?= h($errors['username'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password (Leave blank to keep current)</label>
            <input type="password" name="password" id="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
            <?php if (isset($errors['password'])): ?>
                <p class="mt-2 text-sm text-red-600"><?= h($errors['password'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
            <select id="role" name="role" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md" <?= $id === Session::get('user_id') ? 'disabled' : '' ?>>
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <?php if ($id === Session::get('user_id')): ?>
                <input type="hidden" name="role" value="<?= h($user['role']) ?>">
                <p class="mt-1 text-xs text-gray-500">You cannot change your own role.</p>
            <?php endif; ?>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-primary hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors">
                Update User
            </button>
        </div>
    </form>
</div>
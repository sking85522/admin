<?php
// admin/modules/users/create.php

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $data = [
        'name' => $_POST['name'] ?? '',
        'username' => $_POST['username'] ?? '',
        'password' => $_POST['password'] ?? '',
        'role' => $_POST['role'] ?? 'user',
    ];

    $validator = new Validator();
    if ($validator->validate($data, [
        'name' => 'required',
        'username' => 'required',
        'password' => 'required|min:6'
    ])) {
        // Hash password before saving
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['created_at'] = date('Y-m-d H:i:s');

        $db = new JsonDB(USERS_PATH . '/admin.json');

        // Simple check for existing username
        $exists = false;
        foreach($db->getAll() as $u) {
            if ($u['username'] === $data['username']) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $errors['username'][] = 'Username already exists.';
        } else {
            $db->insert($data);
            Session::setFlash('success', 'User created successfully.');
            redirect(APP_URL . '/users');
        }
    } else {
        $errors = $validator->getErrors();
    }
}
?>

<div class="mb-6">
    <div class="flex items-center">
        <a href="<?= APP_URL ?>/users" class="text-gray-500 hover:text-gray-700 mr-4"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-2xl font-semibold text-gray-900">Create User</h1>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden p-6 max-w-2xl">
    <form action="" method="POST" class="space-y-6">
        <?= Csrf::getTokenField() ?>

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
            <input type="text" name="name" id="name" value="<?= h($_POST['name'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
            <?php if (isset($errors['name'])): ?>
                <p class="mt-2 text-sm text-red-600"><?= h($errors['name'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" name="username" id="username" value="<?= h($_POST['username'] ?? '') ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
            <?php if (isset($errors['username'])): ?>
                <p class="mt-2 text-sm text-red-600"><?= h($errors['username'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" id="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
            <?php if (isset($errors['password'])): ?>
                <p class="mt-2 text-sm text-red-600"><?= h($errors['password'][0]) ?></p>
            <?php endif; ?>
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
            <select id="role" name="role" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                <option value="user" <?= (isset($_POST['role']) && $_POST['role'] === 'user') ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="bg-primary hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors">
                Save User
            </button>
        </div>
    </form>
</div>
<?php
// admin/modules/roles/index.php

Auth::requirePermission('manage_roles');

$db = new JsonDB(CONTENT_PATH . '/roles.json');
$roles = $db->getAll();

$availablePermissions = [
    'view_dashboard', 'manage_posts', 'manage_users', 'manage_settings',
    'manage_roles', 'manage_files', 'view_audit', 'manage_backups',
    'manage_apikeys', 'manage_forms', 'manage_plugins'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) die("Invalid CSRF");

    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $db->delete($_POST['id']);
        Logger::log("Deleted role ID: {$_POST['id']}", 'SYSTEM');
        Session::setFlash('success', 'Role deleted.');
        redirect(APP_URL . '/roles');
    }

    // Create/Edit
    $data = [
        'name' => $_POST['name'],
        'slug' => strtolower(preg_replace('/[^a-z0-9]+/', '', $_POST['name'])),
        'permissions' => $_POST['permissions'] ?? []
    ];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $db->update($_POST['id'], $data);
        Logger::log("Updated role: {$data['name']}", 'SYSTEM');
        Session::setFlash('success', 'Role updated.');
    } else {
        $db->insert($data);
        Logger::log("Created new role: {$data['name']}", 'SYSTEM');
        Session::setFlash('success', 'Role created.');
    }
    redirect(APP_URL . '/roles');
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Roles & Permissions</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permissions</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-bold">Admin</td>
                    <td class="px-6 py-4 text-sm text-gray-500">Super Administrator (All Access)</td>
                    <td class="px-6 py-4 text-right text-sm font-medium text-gray-400">Built-in</td>
                </tr>
                <?php foreach ($roles as $role): ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?= h($role['name']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <div class="flex flex-wrap gap-1">
                            <?php foreach($role['permissions'] ?? [] as $perm): ?>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs"><?= h($perm) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="editRole(<?= htmlspecialchars(json_encode($role)) ?>)" class="text-indigo-600 hover:text-indigo-900 mr-2"><i class="fas fa-edit"></i></button>
                        <form action="" method="POST" class="inline" onsubmit="return confirm('Delete role?');">
                            <?= Csrf::getTokenField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $role['id'] ?>">
                            <button class="text-red-600 hover:text-red-900"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg shadow p-6 h-fit">
        <h2 id="formTitle" class="text-lg font-bold mb-4">Create New Role</h2>
        <form action="" method="POST">
            <?= Csrf::getTokenField() ?>
            <input type="hidden" name="id" id="roleId">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Role Name</label>
                <input type="text" name="name" id="roleName" required class="mt-1 block w-full border border-gray-300 rounded p-2">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                <div class="space-y-2 max-h-64 overflow-y-auto p-2 border rounded">
                    <?php foreach ($availablePermissions as $perm): ?>
                    <label class="flex items-center">
                        <input type="checkbox" name="permissions[]" value="<?= $perm ?>" class="perm-check mr-2 h-4 w-4 text-primary">
                        <span class="text-sm text-gray-700"><?= h(ucwords(str_replace('_', ' ', $perm))) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="w-full bg-primary text-white py-2 rounded hover:bg-blue-600">Save Role</button>
            <button type="button" onclick="resetForm()" class="w-full mt-2 bg-gray-200 text-gray-700 py-2 rounded hover:bg-gray-300">Cancel</button>
        </form>
    </div>
</div>

<script>
function editRole(role) {
    document.getElementById('formTitle').innerText = 'Edit Role';
    document.getElementById('roleId').value = role.id;
    document.getElementById('roleName').value = role.name;

    document.querySelectorAll('.perm-check').forEach(cb => {
        cb.checked = role.permissions && role.permissions.includes(cb.value);
    });
}
function resetForm() {
    document.getElementById('formTitle').innerText = 'Create New Role';
    document.getElementById('roleId').value = '';
    document.getElementById('roleName').value = '';
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = false);
}
</script>
<?php
// admin/modules/forms/index.php

Auth::requirePermission('manage_forms');

$db = new JsonDB(CONTENT_PATH . '/forms.json');
$forms = $db->getAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) die("Invalid CSRF");

    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $db->insert([
                'name' => $_POST['name'],
                'slug' => strtolower(preg_replace('/[^a-z0-9]+/', '-', $_POST['name'])),
                'fields' => [
                    ['type' => 'text', 'label' => 'Name', 'required' => true],
                    ['type' => 'email', 'label' => 'Email', 'required' => true],
                    ['type' => 'textarea', 'label' => 'Message', 'required' => false]
                ],
                'created' => date('Y-m-d H:i:s')
            ]);
            Session::setFlash('success', 'Form created with default fields.');
        } elseif ($_POST['action'] === 'delete') {
            $db->delete($_POST['id']);
            Session::setFlash('success', 'Form deleted.');
        }
    }
    redirect(APP_URL . '/forms');
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Form Builder</h1>

    <form action="" method="POST" class="flex gap-2">
        <?= Csrf::getTokenField() ?>
        <input type="hidden" name="action" value="create">
        <input type="text" name="name" required placeholder="Form Name" class="border rounded px-3 py-1 text-sm">
        <button class="bg-primary text-white px-3 py-1 rounded text-sm hover:bg-blue-600"><i class="fas fa-plus"></i> Create</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($forms as $form): ?>
    <div class="bg-white rounded-lg shadow p-6 border-t-4 border-primary">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h3 class="font-bold text-lg text-gray-900"><?= h($form['name']) ?></h3>
                <code class="text-xs text-gray-500">slug: <?= h($form['slug']) ?></code>
            </div>
            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full"><?= count($form['fields']) ?> Fields</span>
        </div>

        <div class="text-sm text-gray-600 mb-6 border-b pb-4">
            <strong>Endpoint:</strong><br>
            <code class="text-xs bg-gray-100 p-1 rounded block mt-1 select-all"><?= APP_URL ?>/api/submit.php?form=<?= h($form['slug']) ?></code>
        </div>

        <div class="flex justify-between mt-auto">
            <button class="text-primary hover:underline text-sm font-medium" onclick="alert('Form builder UI coming soon. Field schema is saved in JSON.')">Edit Fields</button>
            <form action="" method="POST" onsubmit="return confirm('Delete this form?');">
                <?= Csrf::getTokenField() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $form['id'] ?>">
                <button class="text-red-600 hover:text-red-900 text-sm font-medium"><i class="fas fa-trash"></i> Delete</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if(empty($forms)): ?>
        <div class="col-span-full text-center py-12 text-gray-500 bg-white rounded shadow">
            <i class="fas fa-clipboard-list text-4xl mb-3 text-gray-300"></i>
            <p>No forms created yet. Use the top bar to create one.</p>
        </div>
    <?php endif; ?>
</div>

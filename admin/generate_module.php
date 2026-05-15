<?php
// admin/generate_module.php

require_once 'config.php';
require_once 'core/app.php';

App::init();
Auth::requireLogin();

// Only admin users can generate modules (extra security check)
$user = Auth::user();
if (!$user || $user['role'] !== 'admin') {
    die("Unauthorized.");
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) {
        $error = "Invalid CSRF token.";
    } else {
        $moduleName = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '', $_POST['module_name'] ?? ''));

        if (empty($moduleName)) {
            $error = "Module name is required and must contain only alphanumeric characters.";
        } elseif (file_exists(MODULES_PATH . '/' . $moduleName)) {
            $error = "Module '$moduleName' already exists.";
        } else {
            // Create module directory
            $moduleDir = MODULES_PATH . '/' . $moduleName;
            mkdir($moduleDir, 0755, true);

            // Create data file if requested
            if (isset($_POST['create_db'])) {
                 file_put_contents(CONTENT_PATH . '/' . $moduleName . '.json', json_encode([]));
            }

            // Generate index.php
            $indexContent = "<?php\n// admin/modules/$moduleName/index.php\n\n";
            if (isset($_POST['create_db'])) {
                $indexContent .= "\$db = new JsonDB(CONTENT_PATH . '/$moduleName.json');\n\$items = \$db->getAll();\n?>\n\n";
            } else {
                $indexContent .= "?>\n\n";
            }
            $indexContent .= "<div class=\"flex justify-between items-center mb-6\">\n";
            $indexContent .= "    <h1 class=\"text-2xl font-semibold text-gray-900 capitalize\">$moduleName</h1>\n";
            $indexContent .= "    <a href=\"<?= APP_URL ?>/$moduleName/create\" class=\"bg-primary hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors\">\n";
            $indexContent .= "        <i class=\"fas fa-plus mr-2\"></i> Create Item\n";
            $indexContent .= "    </a>\n";
            $indexContent .= "</div>\n\n";
            $indexContent .= "<div class=\"bg-white rounded-lg shadow p-6\">\n";
            $indexContent .= "    <p class=\"text-gray-600\">Welcome to the $moduleName module.</p>\n";
            if (isset($_POST['create_db'])) {
                 $indexContent .= "    <p class=\"mt-4\">Total items: <?= count(\$items) ?></p>\n";
            }
            $indexContent .= "</div>\n";

            file_put_contents($moduleDir . '/index.php', $indexContent);

            // Generate basic create/edit/delete files
            $basicFileContent = "<?php\n// Action for $moduleName\necho '<h2>Action not fully implemented yet.</h2>';\n";
            file_put_contents($moduleDir . '/create.php', $basicFileContent);
            file_put_contents($moduleDir . '/edit.php', $basicFileContent);
            file_put_contents($moduleDir . '/delete.php', $basicFileContent);

            // Generate config.json
            $configContent = [
                "name" => ucfirst($moduleName),
                "icon" => "fas fa-folder",
                "description" => "Custom module for $moduleName.",
                "version" => "1.0.0"
            ];
            file_put_contents($moduleDir . '/config.json', json_encode($configContent, JSON_PRETTY_PRINT));

            $message = "Module '$moduleName' generated successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Generator - <?= h(APP_NAME) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { colors: { primary: '#3b82f6', } } } }
    </script>
</head>
<body class="bg-gray-100 min-h-screen p-8">

    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl p-8">
        <div class="flex items-center mb-6 border-b pb-4">
            <div class="bg-primary text-white p-3 rounded-full mr-4">
                <i class="fas fa-magic fa-lg"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Module Generator</h1>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= h($message) ?></span>
                <p class="mt-2 text-sm"><a href="<?= APP_URL ?>/<?= h(strtolower($_POST['module_name'])) ?>" class="underline font-bold">Go to new module</a></p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= h($error) ?></span>
            </div>
        <?php endif; ?>

        <form action="generate_module.php" method="POST" class="space-y-6">
            <?= Csrf::getTokenField() ?>

            <div>
                <label for="module_name" class="block text-sm font-medium text-gray-700">Module Name</label>
                <input type="text" name="module_name" id="module_name" required placeholder="e.g. products, events" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                <p class="mt-1 text-xs text-gray-500">Alphanumeric characters only. Will be converted to lowercase.</p>
            </div>

            <div class="flex items-center">
                <input id="create_db" name="create_db" type="checkbox" checked class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                <label for="create_db" class="ml-2 block text-sm text-gray-900">
                    Create corresponding JSON database file
                </label>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Generate Module
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
             <a href="<?= APP_URL ?>/" class="text-sm text-gray-600 hover:text-gray-900"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>

</body>
</html>

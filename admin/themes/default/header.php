<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h(APP_NAME) ?> - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal flex transition-colors duration-300" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark bg-gray-900': darkMode }">
    <style>
        /* Basic dark mode overrides */
        .dark .bg-white { background-color: #1f2937 !important; border-color: #374151; color: #f3f4f6; }
        .dark .bg-gray-100 { background-color: #111827 !important; }
        .dark .bg-gray-200 { background-color: #374151 !important; }
        .dark .text-gray-900 { color: #f9fafb !important; }
        .dark .text-gray-700 { color: #e5e7eb !important; }
        .dark .text-gray-600 { color: #d1d5db !important; }
        .dark .text-gray-500 { color: #9ca3af !important; }
        .dark input, .dark textarea, .dark select { background-color: #374151; border-color: #4b5563; color: white; }
        .dark table td, .dark table th { border-color: #374151; }
        .dark .bg-gray-50 { background-color: #1f2937 !important; }
        .dark .divide-gray-200 > :not([hidden]) ~ :not([hidden]) { border-color: #374151; }
    </style>

    <?php if (Auth::check()): ?>
        <?php include __DIR__ . '/sidebar.php'; ?>
    <?php endif; ?>

    <div class="main-content flex-1 flex flex-col h-screen overflow-hidden">

        <?php if (Auth::check()): ?>
            <?php include __DIR__ . '/navbar.php'; ?>
        <?php endif; ?>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200">
            <div class="container mx-auto px-6 py-8">
                <?php
                $flash = Session::getFlash('success');
                if ($flash):
                ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?= h($flash) ?></span>
                    </div>
                <?php endif; ?>

                <?php
                $flashError = Session::getFlash('error');
                if ($flashError):
                ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?= h($flashError) ?></span>
                    </div>
                <?php endif; ?>

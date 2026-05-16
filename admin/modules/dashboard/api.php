<?php
// admin/modules/dashboard/api.php

// This file would handle specific API requests for the dashboard (e.g., fetching chart data via AJAX)
// For now, it just returns basic stats
$usersDb = new JsonDB(USERS_PATH . '/admin.json');
$postsDb = new JsonDB(CONTENT_PATH . '/posts.json');
$pagesDb = new JsonDB(CONTENT_PATH . '/pages.json');

jsonResponse([
    'users' => count($usersDb->getAll()),
    'posts' => count($postsDb->getAll()),
    'pages' => count($pagesDb->getAll())
]);

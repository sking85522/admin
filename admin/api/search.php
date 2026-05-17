<?php
// admin/api/search.php

require_once '../config.php';
require_once '../core/app.php';

header('Content-Type: application/json');
Auth::requireLogin();

$query = $_GET['q'] ?? '';
$results = [];

if (strlen($query) >= 2) {
    $query = strtolower($query);

    // Search Posts
    if (Auth::hasPermission('manage_posts')) {
        $postsDb = new JsonDB(CONTENT_PATH . '/posts.json');
        foreach ($postsDb->getAll() as $post) {
            if (strpos(strtolower($post['title'] ?? ''), $query) !== false || strpos(strtolower($post['content'] ?? ''), $query) !== false) {
                $results[] = [
                    'type' => 'Post',
                    'title' => $post['title'],
                    'url' => APP_URL . '/posts/edit/' . $post['id'],
                    'icon' => 'fa-pen'
                ];
            }
        }
    }

    // Search Users
    if (Auth::hasPermission('manage_users')) {
        $usersDb = new JsonDB(USERS_PATH . '/admin.json');
        foreach ($usersDb->getAll() as $user) {
            if (strpos(strtolower($user['username'] ?? ''), $query) !== false || strpos(strtolower($user['name'] ?? ''), $query) !== false) {
                $results[] = [
                    'type' => 'User',
                    'title' => $user['username'] . ' (' . ($user['name'] ?? '') . ')',
                    'url' => APP_URL . '/users/edit/' . $user['id'],
                    'icon' => 'fa-user'
                ];
            }
        }
    }
}

jsonResponse(['success' => true, 'results' => array_slice($results, 0, 10)]); // Limit to top 10

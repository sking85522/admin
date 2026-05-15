<?php
// admin/modules/posts/delete.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $id = $_POST['id'] ?? null;

    if ($id) {
        $db = new JsonDB(CONTENT_PATH . '/posts.json');
        if ($db->delete($id)) {
            Session::setFlash('success', 'Post deleted successfully.');
        } else {
            Session::setFlash('error', 'Failed to delete post.');
        }
    }
}

redirect(APP_URL . '/posts');

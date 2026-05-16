<?php
// admin/modules/users/delete.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $id = $_POST['id'] ?? null;

    if ($id) {
        if ($id == Session::get('user_id')) {
            Session::setFlash('error', 'You cannot delete yourself.');
        } else {
            $db = new JsonDB(USERS_PATH . '/admin.json');
            if ($db->delete($id)) {
                Session::setFlash('success', 'User deleted successfully.');
            } else {
                Session::setFlash('error', 'Failed to delete user.');
            }
        }
    }
}

redirect(APP_URL . '/users');

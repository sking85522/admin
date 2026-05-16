<?php
// admin/api/auth.php

require_once '../config.php';
require_once '../core/app.php';

header('Content-Type: application/json');

// Extremely simple token based auth for API
// In a real app, use proper JWT. Here we'll just verify admin credentials and return a fake token for demonstration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (Auth::login($username, $password)) {
        // Return a mock token based on session
        jsonResponse([
            'success' => true,
            'token' => base64_encode($username . ':' . time()),
            'user' => [
                'id' => Session::get('user_id'),
                'username' => $username
            ]
        ]);
    } else {
        jsonResponse(['success' => false, 'error' => 'Invalid credentials'], 401);
    }
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

<?php
// admin/api/upload.php

require_once '../config.php';
require_once '../core/app.php';

header('Content-Type: application/json');

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploader = new Uploader();
    $result = $uploader->upload('file');

    if ($result['success']) {
        jsonResponse($result);
    } else {
        jsonResponse($result, 400);
    }
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

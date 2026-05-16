<?php
// admin/core/uploader.php

class Uploader {
    private $uploadPath;
    private $allowedTypes;
    private $maxSize;
    // Enforce safe extensions mapped to allowed MIME types
    private $allowedExtensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];

    public function __construct($uploadPath = UPLOADS_PATH, $maxSize = 5242880) { // Default 5MB
        $this->uploadPath = rtrim($uploadPath, '/') . '/';
        $this->allowedTypes = array_keys($this->allowedExtensions);
        $this->maxSize = $maxSize;

        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    public function upload($fileInputName) {
        if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'No file uploaded or upload error.'];
        }

        $file = $_FILES[$fileInputName];

        if ($file['size'] > $this->maxSize) {
            return ['success' => false, 'error' => 'File size exceeds limit.'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedTypes)) {
            return ['success' => false, 'error' => 'Invalid file type.'];
        }

        // Force a safe extension based on the verified MIME type, ignore user input extension
        $extension = $this->allowedExtensions[$mimeType];
        $filename = uniqid('img_') . '.' . $extension;
        $destination = $this->uploadPath . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'filename' => $filename, 'path' => APP_URL . '/storage/uploads/' . $filename];
        }

        return ['success' => false, 'error' => 'Failed to move uploaded file.'];
    }
}

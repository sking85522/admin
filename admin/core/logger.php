<?php
// admin/core/logger.php

class Logger {
    private static $logFile = LOGS_PATH . '/app.log';

    public static function log($message, $level = 'INFO') {
        if (!is_dir(LOGS_PATH)) {
            mkdir(LOGS_PATH, 0755, true);
        }

        $date = date('Y-m-d H:i:s');
        $user = Session::get('username', 'System');

        // Structured logging for the new audit module
        $logEntry = [
            'id' => uniqid(),
            'time' => $date,
            'level' => $level,
            'user' => $user,
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'CLI'
        ];

        // Maintain old plain text log for legacy/server parsing
        $formattedMessage = "[$date] [$level] [User: $user] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $formattedMessage, FILE_APPEND);

        // Save to structured JSON DB for the Audit module
        $auditDbFile = LOGS_PATH . '/audit.json';
        if (!file_exists($auditDbFile)) file_put_contents($auditDbFile, json_encode([]));

        $data = json_decode(file_get_contents($auditDbFile), true);
        array_unshift($data, $logEntry); // Add to beginning

        // Keep only last 1000 logs to prevent bloat
        if (count($data) > 1000) {
            $data = array_slice($data, 0, 1000);
        }

        file_put_contents($auditDbFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    public static function error($message) {
        self::log($message, 'ERROR');
    }
}

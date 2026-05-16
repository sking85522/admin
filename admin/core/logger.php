<?php
// admin/core/logger.php

class Logger {
    private static $logFile = LOGS_PATH . '/app.log';

    public static function log($message, $level = 'INFO') {
        if (!is_dir(LOGS_PATH)) {
            mkdir(LOGS_PATH, 0755, true);
        }

        $date = date('Y-m-d H:i:s');
        $formattedMessage = "[$date] [$level] $message" . PHP_EOL;

        file_put_contents(self::$logFile, $formattedMessage, FILE_APPEND);
    }

    public static function error($message) {
        self::log($message, 'ERROR');
    }
}

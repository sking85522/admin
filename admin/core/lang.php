<?php
// admin/core/lang.php

class Lang {
    private static $translations = [];
    private static $currentLang = 'en';

    public static function load($lang = 'en') {
        self::$currentLang = $lang;
        $file = __DIR__ . '/../lang/' . $lang . '.json';

        if (file_exists($file)) {
            self::$translations = json_decode(file_get_contents($file), true) ?: [];
        } else {
            self::$translations = [];
        }
    }

    public static function get($key, $default = null) {
        return self::$translations[$key] ?? $default ?? $key;
    }
}

// Global helper function for easier translation in views
function __($key, $default = null) {
    return h(Lang::get($key, $default));
}

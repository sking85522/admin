<?php
// admin/core/app.php

require_once 'session.php';
require_once 'auth.php';
require_once 'csrf.php';
require_once 'jsondb.php';
require_once 'validator.php';
require_once 'uploader.php';
require_once 'logger.php';
require_once 'helper.php';
require_once 'plugin.php'; // Load the new plugin architecture
require_once 'lang.php';
require_once 'updater.php';

class App {
    public static function init() {
        Session::start();

        // Setup Localization
        $db = new JsonDB(CONTENT_PATH . '/settings.json');
        $settings = $db->getAll();
        $lang = 'en';
        foreach($settings as $s) {
            if(isset($s['key']) && $s['key'] === 'language') $lang = $s['value'];
        }
        Lang::load($lang);

        // Setup initial default roles if they don't exist
        $rolesFile = CONTENT_PATH . '/roles.json';
        if (!file_exists($rolesFile)) {
            file_put_contents($rolesFile, json_encode([
                [
                    'id' => '1',
                    'name' => 'Editor',
                    'slug' => 'editor',
                    'permissions' => ['view_dashboard', 'manage_posts', 'manage_files']
                ]
            ], JSON_PRETTY_PRINT));
        }
    }
}

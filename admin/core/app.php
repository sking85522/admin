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

class App {
    public static function init() {
        Session::start();
    }
}

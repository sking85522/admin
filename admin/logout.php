<?php
// admin/logout.php

require_once 'config.php';
require_once 'core/app.php';

App::init();

if (Auth::check()) {
    Logger::log("User '" . Session::get('username') . "' logged out.");
    Auth::logout();
}

redirect(APP_URL . '/login.php');
<?php
// admin/core/auth.php

require_once 'session.php';
require_once 'jsondb.php';

class Auth {
    public static function login($username, $password) {
        $db = new JsonDB(USERS_PATH . '/admin.json');
        $users = $db->getAll();

        foreach ($users as $user) {
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                Session::set('user_id', $user['id']);
                Session::set('username', $user['username']);
                return true;
            }
        }
        return false;
    }

    public static function check() {
        return Session::get('user_id') !== null;
    }

    public static function logout() {
        Session::destroy();
    }

    public static function user() {
        if (!self::check()) return null;

        $db = new JsonDB(USERS_PATH . '/admin.json');
        return $db->getById(Session::get('user_id'));
    }

    public static function requireLogin() {
        if (!self::check()) {
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
    }
}

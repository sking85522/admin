<?php
// admin/core/csrf.php

require_once 'session.php';

class Csrf {
    public static function generateToken() {
        if (!Session::get('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    public static function verifyToken($token) {
        $storedToken = Session::get('csrf_token');
        return $storedToken && hash_equals($storedToken, $token);
    }

    public static function getTokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

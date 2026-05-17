<?php
// admin/core/auth.php

require_once 'session.php';
require_once 'jsondb.php';
require_once 'logger.php';

class Auth {
    public static function login($username, $password) {
        $db = new JsonDB(USERS_PATH . '/admin.json');
        $users = $db->getAll();

        foreach ($users as $user) {
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                // If 2FA is enabled globally, we put them in a holding state
                $settingsDb = new JsonDB(CONTENT_PATH . '/settings.json');
                $is2faEnabled = false;
                foreach($settingsDb->getAll() as $s) {
                    if(isset($s['key']) && $s['key'] === 'require_2fa' && $s['value'] === 'true') $is2faEnabled = true;
                }

                if ($is2faEnabled) {
                    Session::set('pending_2fa_user_id', $user['id']);
                    Session::set('pending_2fa_username', $user['username']);
                    Session::set('pending_2fa_role', $user['role'] ?? 'user');
                    return '2fa_required';
                }

                self::completeLogin($user);
                return true;
            }
        }
        Logger::log("Failed login attempt for '$username'.", 'WARNING');
        return false;
    }

    public static function completeLogin($user) {
        Session::set('user_id', $user['id'] ?? Session::get('pending_2fa_user_id'));
        Session::set('username', $user['username'] ?? Session::get('pending_2fa_username'));
        Session::set('role', $user['role'] ?? Session::get('pending_2fa_role', 'user'));

        Session::remove('pending_2fa_user_id');
        Session::remove('pending_2fa_username');
        Session::remove('pending_2fa_role');

        // Track login history
        $historyDb = new JsonDB(LOGS_PATH . '/login_history.json');
        $historyDb->insert([
            'user_id' => Session::get('user_id'),
            'username' => Session::get('username'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'time' => date('Y-m-d H:i:s'),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);

        Logger::log("User '" . Session::get('username') . "' logged in successfully.", 'AUTH');

        // Fire webhook event if webhook class exists (handled dynamically)
        if (class_ready('Webhook')) Webhook::fire('user.login', ['username' => Session::get('username')]);
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

    // RBAC: Check if current user has a specific permission
    public static function hasPermission($permission) {
        $userRole = Session::get('role', 'user');

        // Super admin always has access
        if ($userRole === 'admin') {
            return true;
        }

        $rolesDb = new JsonDB(CONTENT_PATH . '/roles.json');
        $roles = $rolesDb->getAll();

        foreach ($roles as $role) {
            if ($role['slug'] === $userRole) {
                return in_array($permission, $role['permissions'] ?? []);
            }
        }

        return false;
    }

    // RBAC: Redirect if missing permission
    public static function requirePermission($permission) {
        if (!self::hasPermission($permission)) {
            Session::setFlash('error', 'You do not have permission to perform this action.');
            header('Location: ' . APP_URL . '/');
            exit;
        }
    }
}

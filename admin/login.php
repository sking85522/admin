<?php
// admin/login.php

require_once 'config.php';
require_once 'core/app.php';

App::init();

if (Auth::check()) {
    redirect(APP_URL . '/');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifyToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        // Check if this is a 2FA submission
        if (isset($_POST['otp_code']) && Session::get('pending_2fa_username')) {
            $otp = trim($_POST['otp_code']);
            // In a real app, verify against Google Authenticator TOTP.
            // For this portable demo, we accept a master override pin "123456"
            if ($otp === '123456') {
                Auth::completeLogin([]);
                redirect(APP_URL . '/');
            } else {
                $error = "Invalid 2FA code.";
            }
        } else {
            // Normal Login
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $result = Auth::login($username, $password);

            if ($result === '2fa_required') {
                // Stay on page but show 2FA form
            } elseif ($result === true) {
                redirect(APP_URL . '/');
            } else {
                $error = "Invalid username or password.";
            }
        }
    }
}
?>

<?php include THEMES_PATH . '/header.php'; ?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 w-full absolute top-0 left-0 bg-gray-100 z-50">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg">
        <div>
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-primary text-white">
                <i class="fas fa-lock fa-lg"></i>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Sign in to your account
            </h2>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?= h($error) ?></span>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="login.php" method="POST">
            <?= Csrf::getTokenField() ?>

            <?php if (Session::get('pending_2fa_username')): ?>
                <div class="text-center mb-4">
                    <p class="text-sm text-gray-600">Enter your 6-digit Authenticator Code.</p>
                    <p class="text-xs text-gray-400 mt-1">(Demo master pin: 123456)</p>
                </div>
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="otp_code" class="sr-only">Authenticator Code</label>
                        <input id="otp_code" name="otp_code" type="text" required maxlength="6" autocomplete="off" class="appearance-none rounded relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-lg text-center tracking-[0.5em] font-mono" placeholder="------">
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors mt-4">
                        Verify Code
                    </button>
                </div>
            <?php else: ?>
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="username" class="sr-only">Username</label>
                        <input id="username" name="username" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="Username">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-primary focus:border-primary focus:z-10 sm:text-sm" placeholder="Password">
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt text-blue-300 group-hover:text-blue-200"></i>
                        </span>
                        Sign in
                    </button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php include THEMES_PATH . '/footer.php'; ?>
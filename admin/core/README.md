# Portable Admin Core

This directory contains the core framework classes that power the admin panel.

## Architecture
- **`app.php`**: The main bootstrap file. Initializes the session, locale, and handles early bootstrapping.
- **`auth.php`**: Handles authentication and Role-Based Access Control (RBAC). Uses `admin.json` and `roles.json` for validation.
- **`csrf.php`**: Protects against Cross-Site Request Forgery attacks.
- **`helper.php`**: Contains global helper functions like `h()` for escaping output, `redirect()`, and `__()` for localization.
- **`jsondb.php`**: The custom lightweight flat-file JSON database engine. Features `insert()`, `update()`, `delete()`, and `getAll()`.
- **`lang.php`**: The localization engine. Loads translations from the `admin/lang/` directory based on the user's settings.
- **`logger.php`**: A dual-logging system that writes flat files (`app.log`) and structured JSON (`audit.json`) for the Audit module.
- **`plugin.php`**: Architecture to install and extract new modules from `.zip` files safely.
- **`session.php`**: Manages secure sessions and flash messages.
- **`uploader.php`**: Secure file upload handling. It verifies files via `finfo_file` (MIME checking) and strictly forces safe extensions to prevent RCE attacks.

## Security Notes
**Do not remove `.htaccess` rules.** The `.htaccess` file in the root `admin/` directory explicitly prevents direct HTTP access to the `core/` folder to protect sensitive logic.
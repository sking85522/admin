# Storage Directory

This directory acts as the entire database and file storage system for the Portable Admin Panel. It requires **write permissions (0755 or 0777)** from the PHP web server process.

## Subdirectories

- **`content/`**: Stores public-facing data schemas (posts, pages, settings, roles, forms, plugins).
- **`users/`**: Stores highly sensitive user credentials (`admin.json`).
- **`uploads/`**: Publicly accessible directory for images uploaded via the Media Manager or TinyMCE.
- **`logs/`**: System generated logs (`app.log` and `audit.json`).
- **`backups/`**: Generated `.zip` files created by the Backups module.

## Security
Direct access to `.json` files is strictly blocked by the `admin/.htaccess` file. Do not alter those rules, or your raw database files will be readable by the public.
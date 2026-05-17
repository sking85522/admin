# Modules Directory

This is where all functional sections of the Admin Panel live. The architecture is designed to be completely modular and plug-and-play.

## Creating a Module
You can use the built-in "Module Generator" (`admin/generate_module.php`) to scaffold new modules instantly.

A standard module contains:
- `index.php`: The main list view (Read).
- `create.php`: Form to add new items.
- `edit.php`: Form to edit items.
- `delete.php`: Action file to remove items.
- `config.json`: Metadata about the module (name, icon, version) used by the Plugin Manager and Sidebar (in future iterations).

## RBAC Permissions
If you are adding a new module manually, ensure you add the corresponding `manage_[module_name]` permission to `core/app.php`'s default role seeder and the `modules/roles/index.php` available permissions array so users can be granted access.
# Internship Security Application

This is a PHP/MySQL internship project for learning secure web application development. The current application manages users, payroll uploads, payment status, and simple reports.

## Setup

Install the tools listed in `SOFTWARE_REQUIREMENTS.md`, then:

1. Copy `config.example.php` to `config.php`.
2. Update `config.php` with local MySQL credentials.
3. Import `schema.sql` into MySQL to create the `mysite` database and required tables.
4. Serve the folder with a PHP-capable web server.

## Mentor Notes

This code is a useful student starting point, but it still needs security hardening before real use:

- Add CSRF protection for state-changing actions.
- Escape output with `htmlspecialchars` before rendering user/database data.
- Replace GET-based payment actions with POST requests.
- Add file upload validation for payroll imports.
- Add database migrations or a `schema.sql` file.
- Disable detailed error display in production.

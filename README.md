# Internship Security Application

This is a PHP/MySQL internship project for learning secure web application development. The current application manages users, payroll uploads, payment status, and simple reports.

## Setup

Install the tools listed in `SOFTWARE_REQUIREMENTS.md`, then:

1. Copy `config.example.php` to `config.php`.
2. Update `config.php` with local MySQL credentials.
3. Import `schema.sql` into MySQL to create the `mysite` database and required tables.
4. Serve the folder with a PHP-capable web server.
5. Open `frontend/login.php` in the browser.

Example with PHP's built-in server:

```powershell
php -S 127.0.0.1:8000
```

Then open:

```text
http://127.0.0.1:8000/frontend/login.php
```

## Project Structure

```text
backend/
  database.php

frontend/
  admin.php
  login.php
  logout.php
  register.php
  report.php
  user.php
  welcome.php
```

`backend/` contains shared server-side code. `frontend/` contains the pages opened in the browser.

## Mentor Notes

This code is a useful student starting point, but it still needs security hardening before real use:

- Add CSRF protection for state-changing actions.
- Escape output with `htmlspecialchars` before rendering user/database data.
- Replace GET-based payment actions with POST requests.
- Add file upload validation for payroll imports.
- Add database migrations or a `schema.sql` file.
- Disable detailed error display in production.

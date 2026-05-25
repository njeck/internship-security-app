# What Was Added for the Student

This file explains what the mentor added or changed before the project was uploaded to GitHub.

## Git Repository Setup

The project was turned into a Git repository so the student and mentor can track changes together.

Commits already created:

- `Initial internship security app`
- `Add student internship work plan`
- `Document software requirements`

## Removed Hardcoded Database Password

The original PHP files contained a real MySQL password directly in the source code. That is unsafe for GitHub.

What changed:

- Added `config.example.php`.
- Added `.gitignore` so real `config.php` is not committed.
- Moved database connection code into `backend/database.php`.

The student must create their own local `config.php` from `config.example.php`.

## Added Database Schema

Added `schema.sql` so the student can create the database tables more easily.

It includes tables for:

- `users`
- `payrolls`
- `customers`
- `payments`

## Added Project Documentation

Added:

- `README.md` for general setup.
- `SOFTWARE_REQUIREMENTS.md` for required software.
- `WORK_PLAN.md` for the 7-working-day internship plan.
- `CHANGELOG_FOR_STUDENT.md` to explain mentor changes.

## Installed and Checked Tools

On the mentor machine:

- Git was already installed.
- GitHub CLI was installed.
- PHP 8.3 was installed.
- PHP syntax checks were run on the project files.

The database server still needs to be installed manually, preferably with XAMPP, WampServer, Laragon, MySQL, or MariaDB.

## Separated Backend and Frontend

The project is now separated into two main areas:

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

Backend means server-side support code, database connection, authentication helpers, and future security functions.

Frontend means the PHP pages the user opens in the browser. These files still contain some backend logic, but the structure is now ready for the student to continue separating logic from display.

## What the Student Should Work on Next

High-priority next steps:

- Escape output using `htmlspecialchars`.
- Change payment actions from GET links to POST forms.
- Add CSRF token protection.
- Validate CSV upload files.
- Fix the JavaScript search on the user page.
- Improve role checks and error handling.


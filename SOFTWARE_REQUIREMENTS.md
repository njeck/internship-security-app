# Software Requirements

This project is a basic PHP/MySQL web application. Install these tools before running or contributing.

## Required

- Git
- PHP 8.3 or newer
- MySQL or MariaDB server
- A web server that can serve PHP files, such as Apache, XAMPP, WampServer, Laragon, or PHP's built-in development server
- GitHub CLI, optional but recommended for uploading and managing the GitHub repository

## Installed on This Machine

- Git: installed
- PHP 8.3: installed with WinGet
- GitHub CLI: installed with WinGet
- MySQL/MariaDB server: not completed by WinGet; install manually with XAMPP, WampServer, Laragon, MySQL, or MariaDB

## Windows Install Commands

GitHub CLI:

```powershell
winget install --id GitHub.cli --accept-source-agreements --accept-package-agreements
```

PHP 8.3:

```powershell
winget install --id PHP.PHP.8.3 --accept-source-agreements --accept-package-agreements
```

XAMPP, optional all-in-one local server:

```powershell
winget install --id ApacheFriends.Xampp.8.2 --accept-source-agreements --accept-package-agreements
```

If XAMPP does not install correctly from the terminal, install it from the official installer and start Apache and MySQL from the XAMPP Control Panel.

## Project Setup After Installing Software

1. Copy `config.example.php` to `config.php`.
2. Update `config.php` with the local database username and password.
3. Import `schema.sql` into MySQL/MariaDB.
4. Open the project through Apache/XAMPP, or run PHP's built-in server from the project folder:

```powershell
php -S 127.0.0.1:8000
```

5. Visit `http://127.0.0.1:8000/frontend/login.php`.

## GitHub Upload

After GitHub CLI is installed, log in:

```powershell
gh auth login
```

Then create or connect a repository and push the commits.

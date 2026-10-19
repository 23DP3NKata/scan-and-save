# Scan & Save

Scan & Save is a web application for storing purchase documents: receipts, warranty cards and other files. Users upload PDF/JPEG/PNG files, link them to categories and products, search and filter them, and receive reminders before a warranty expires. Administrators manage users, documents and system settings.

## Tech stack

- **Frontend:** Vue 3, TypeScript, Vite, Vuetify, Pinia, Vue Router (`frontend/`)
- **Backend:** Laravel 13, PHP 8.3+ (`backend/`)
- **Database:** SQLite for local development

The frontend talks only to the Laravel API under `/api`. In development, Vite proxies `/api` to Laravel on port 8000. Authentication uses a Laravel session cookie with a CSRF token.

## Requirements

- PHP 8.3+ with the `pdo_sqlite` and `sqlite3` extensions
- Composer
- Node.js 22 and npm

## Setup

Backend:

```sh
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

On Windows, use `copy .env.example .env` instead of `cp`. With the default SQLite connection, `php artisan migrate` offers to create `database/database.sqlite` if it does not exist yet.

Frontend:

```sh
cd frontend
npm ci
```

## Running the application

Terminal 1 — API on <http://127.0.0.1:8000>:

```sh
cd backend/public
php -d upload_max_filesize=10M -d post_max_size=12M -S 127.0.0.1:8000 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
```

The `-d` options raise PHP's upload limits so that files up to 10 MB can be uploaded. Plain `php artisan serve` also works, but uses the default PHP limits (often 2 MB).

Terminal 2 — frontend on <http://localhost:5176>:

```sh
cd frontend
npm run dev
```

Terminal 3 (optional) — daily warranty reminders:

```sh
cd backend
php artisan schedule:work
```

Reminders run every day at 08:00 Europe/Riga. To run them once manually: `php artisan scan:send-reminders`. With `MAIL_MAILER=log`, emails are written to `storage/logs/laravel.log` instead of being sent.

## Administrator account

Registration always creates a normal user. Create an administrator from the terminal:

```sh
cd backend
php artisan scan:create-admin admin@example.test --name=Administrator
```

The command asks for a password (at least 12 characters) twice.

## Backups (SQLite)

```sh
cd backend
php artisan scan:backup
php artisan scan:restore <backup-directory> --verify
```

`scan:backup` writes a backup to `storage/app/private/backups`. Restoring requires maintenance mode (`php artisan down`) and the `--force` flag.

## Tests and build

```sh
cd backend
php artisan test
```

```sh
cd frontend
npm run build
```

Backend tests use an in-memory SQLite database.

## Team

- **Nikolajs Katane** — team lead: repository, Trello, pull request reviews, database, documentation
- **Aleksandr Getmanenko** — frontend
- **Leonids Katane** — backend

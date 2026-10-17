# Laravel Boilerplate

A starting point for Laravel 13 projects with a separate **admin panel**, **user area**, roles and
permissions, an activity log and swappable **Bootstrap 5 themes** with light/dark mode.

Inspired by [rappasoft/laravel-boilerplate](https://github.com/rappasoft/laravel-boilerplate), rebuilt from scratch.
See [CREDITS.md](CREDITS.md).

## Features

- **Areas with their own login and session.** `admin` and `web` (users) each have a guard, a session
  cookie, a session table and a timeout (`config/areas.php`). Logging out of one never logs you out of the other.
  New areas (e.g. a client portal) are added by config + a routes folder: [docs/adding-an-area.md](docs/adding-an-area.md).
- **Folder-based routes.** `routes/admin/*.php` are protected automatically; `routes/admin/public/*.php` are not.
- **Roles & permissions** (spatie/laravel-permission). Code checks **permissions only**, never role names.
  Roles are managed in the admin panel. Super Admin passes every check.
- **Admin management:** admins, roles, deactivate/reactivate, log out of all devices, soft delete/restore.
- **Activity log** of admin actions and logins, and **System Logs** (Laravel log viewer, Super Admin only).
- **Themes:** pages only use `<x-ui::…>` components, so a theme can be swapped per area.
  Ships with `gradient-able` (dark blue) and an example `gradient-able-sunset-orange`.
  Light/dark/auto mode, charts (`<x-ui::chart>`), icon browser. See [docs/creating-a-theme.md](docs/creating-a-theme.md).
- **Feature tests** for sessions, auth, permissions and themes.

## Requirements

| | Version |
|---|---|
| PHP | 8.3+ (8.4 recommended, e.g. via [Laravel Herd](https://herd.laravel.com)) |
| Composer | 2 |
| Node | 24 (pinned in `.nvmrc`. With nvm: `nvm use`) |
| Database | SQLite by default (nothing to install). MySQL/PostgreSQL work too |

## Installation

```bash
git clone git@github.com:jmicarus12/laravel-gradient-boilerplate.git my-project
cd my-project

# 1. PHP dependencies and environment
composer install
cp .env.example .env
php artisan key:generate

# 2. Database: creates the tables, demo accounts and permissions
touch database/database.sqlite
php artisan migrate --seed

# 3. Frontend (needs Node 24)
nvm use
npm install
npm run build

# 4. Run it
php artisan serve
```

Open http://127.0.0.1:8000.

> Using MySQL instead of SQLite? Set `DB_CONNECTION=mysql` and the `DB_*` values in `.env`,
> create the database, and skip the `touch` line.

## Logging in

| Area | URL | Demo account |
|---|---|---|
| Admin panel | http://127.0.0.1:8000/admin | `admin@example.com` / `password` (Super Admin) |
| User area | http://127.0.0.1:8000/login | `test@example.com` / `password` |

**Change these passwords** (or don't seed them) on any server other than your own machine.

## Everyday commands

| Command | When |
|---|---|
| `php artisan serve` | run the app |
| `npm run dev` | while editing SCSS/JS/Blade: rebuilds and refreshes the browser automatically |
| `npm run build` | after changing SCSS/JS, adding a theme, or before deploying |
| `php artisan permissions:sync` | after adding/removing a case in `app/Enums/Permission.php` |
| `php artisan test` | run the tests (uses an in-memory database, never your dev data) |
| `php artisan optimize:clear` | if config/routes seem "stuck" |

**Don't run `php artisan optimize` or `config:cache` on your dev machine.** Cached config makes `.env`
changes invisible until you clear it. The tests refuse to run while config is cached, to protect your database.
Caching is for production.

## Configuration (`.env`)

| Key | Default | |
|---|---|---|
| `THEME_WEB`, `THEME_ADMIN` | `gradient-able` | theme folder in `resources/themes/` per area |
| `ADMIN_SESSION_LIFETIME` | `30` | admin idle timeout in minutes (users: `SESSION_LIFETIME`) |
| `ADMIN_SESSION_COOKIE` | `{app}-admin-session` | admin session cookie name |
| `MAIL_*` | `log` | emails (password reset, verification) go to `storage/logs/laravel.log` until you set a real mailer |

## Where things are

| | |
|---|---|
| `config/areas.php` | areas: guard, theme, URL prefix, routes folder, session settings |
| `config/menu.php` | sidebar and top-bar menu items |
| `app/Enums/Permission.php` | the list of all permissions |
| `app/Services/` | business actions (create admin, sync roles…) |
| `app/Policies/` | who may do what to which record |
| `routes/admin/` | admin routes (`public/` = no login needed) |
| `resources/views/` | pages |
| `resources/themes/` | themes: layouts, components, SCSS/JS |
| `docs/` | [PLAN.md](docs/PLAN.md), [adding-an-area.md](docs/adding-an-area.md), [creating-a-theme.md](docs/creating-a-theme.md) |

## Deploying (short version)

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan permissions:sync
npm ci && npm run build
php artisan optimize
```

Set `APP_ENV=production`, `APP_DEBUG=false` and a real `APP_URL` in the server's `.env`.

## Credits

Third-party code, templates and their licenses: [CREDITS.md](CREDITS.md).

# Laravel Boilerplate — Plan

Our own Laravel 13 boilerplate. Inspired by [rappasoft/laravel-boilerplate](https://github.com/rappasoft/laravel-boilerplate) (MIT)
and the old Bee-Force CMS, rebuilt cleanly. Built step by step so every piece is understood.

## Stack

| Piece | Choice | Why |
|---|---|---|
| Framework | Laravel 13 | Current major (bug fixes to Q3 2027, security to Mar 2028) |
| PHP | 8.4 (Herd) | Laravel 13 needs 8.3+ |
| Node | 24 (pinned in `.nvmrc`) | Vite needs a modern Node; global default (16) is too old |
| Database (dev) | SQLite | Zero setup; switch to MySQL per project |
| Views | Blade + controllers + Form Requests | Backend-first, no frontend framework to learn |
| CSS | Bootstrap 5.3 + Gradient Able (free, MIT) via Vite + Sass | Familiar Bootstrap classes, same layout style as Troy's admin |
| Theme | Dark blue + white, all brand colors in `resources/scss/_brand.scss` | One file to restyle a new project |
| Dark mode | Bootstrap 5.3 color modes (`data-bs-theme`) + header toggle | Follows system by default, remembers choice |
| Roles & permissions | spatie/laravel-permission | Same as before, used properly this time |
| Activity log | spatie/laravel-activitylog | Kept from rappasoft |
| Tests | PHPUnit feature tests | Prove sessions/permissions work |

No starter kit, no Tailwind, no Livewire for now. Can be added later.

## Themes (swappable templates)

Pages never contain template HTML. Two layers:

```
resources/views/             ← PAGES: content only, use <x-ui::layouts.admin>, <x-ui::card>, <x-ui::table>, ...
resources/themes/<name>/     ← THEME: all template HTML + CSS
    components/layouts/  admin, user, guest
    components/  page-header, card, stat-card, table, button, input, checkbox, alert, badge, flash
    partials/  head, sidebar, menu, user menu, theme toggle, footer
    scss/  _brand.scss, app.scss (incl. dark mode)
```

```php
// config/areas.php — each area names its theme
'admin' => [..., 'theme' => 'gradient-able'],
'web'   => [..., 'theme' => 'gradient-able'],
```

- `<x-ui::card>` resolves to `resources/themes/{active}/components/card.blade.php`; `->middleware('area:admin')` picks the area (session, guard and theme). Details: `resources/themes/README.md`.
- Menus live in `config/menu.php` (not in the theme), so every theme shows the same items.
- Each theme has its own Vite entry (`resources/themes/<name>/scss/app.scss`, `js/app.js`).
- **New template** = copy a theme folder, rewrite the layouts + ~10 components with the new template's HTML, change config. Pages untouched.
- Rule: keep the component list small; pages must not grow their own template markup.
- Guide: `docs/creating-a-theme.md`.

## User side (`web` area)

Same Gradient Able theme, lighter **user layout** (top nav, no admin sidebar).
- Home (after login)
- Profile settings: name, email, password, delete account

## Core idea: Areas

An **area** is a part of the app with its own users, login, and session.
We start with two:

| Area | URLs | Guard | Users table | Session cookie | Sessions table |
|---|---|---|---|---|---|
| `web` (users) | `/*` | `web` | `users` | `app_session` | `sessions` |
| `admin` | `/admin/*` (routes in `routes/admin/*.php`) | `admin` | `admins` | `app_admin_session` | `admin_sessions` |

Everything about an area lives in **one config entry**:

```php
// config/areas.php
'admin' => [
    'prefix'   => 'admin',
    'guard'    => 'admin',
    'cookie'   => 'app_admin_session',
    'table'    => 'admin_sessions',   // fully separate session storage
    'lifetime' => 30,                 // minutes
    'login'    => 'admin.login',      // guests get redirected here
    'home'     => 'admin.dashboard',  // after login
],
```

One middleware — `UseAreaSession` — runs **before** the session starts and, based on the URL:
1. sets the session cookie name, table and lifetime,
2. sets the default guard (`Auth::shouldUse`) so `auth()->user()` is the right kind of user,
3. sends guests to that area's login page.

Result: log into both areas in one browser → two cookies, two sessions. Logging out of one never touches the other.
Adding a new area (e.g. `client`) = one config entry + model + guard + routes + login screens. No new session logic.

## Admin features (kept from rappasoft, done properly)

- Admin users: list / search, create, edit, deactivate / reactivate, delete / restore, change password, clear sessions, impersonate (later)
- Roles: create / edit, tick permissions in the UI
- **Routes check permissions, never role names** (old CMS had 129 role references in routes)
- Permissions defined in a PHP enum + seeder (single source of truth, autocomplete, no typos)
- Policies per model → same rules for `->can()` on routes and `@can` in Blade
- Super admin bypasses all checks via `Gate::before`
- Roles/permissions are per guard (spatie `guard_name = admin`)
- Activity log of admin actions

## Build steps

Steps 1–11 are done. Guides: [`adding-an-area.md`](adding-an-area.md), [`creating-a-theme.md`](creating-a-theme.md).

Each step teaches one thing. Ask "why" anytime.

| # | Step | You learn |
|---|---|---|
| 1 | Fresh Laravel 13 app, `.nvmrc`, `git init`, `gitswitch personal`, first commit | Project structure |
| 2 | Theme system + `gradient-able` theme: Bootstrap 5.3 via Vite, `_brand.scss`, admin / user / guest layouts, core components, dark-mode toggle | Blade components, view namespaces, Vite/Sass, Bootstrap color modes |
| 3 | `web` area: register / login / logout, Home, Profile settings | Default auth: guard → provider → model |
| 4 | `admins` table + model, `admin` guard + provider | What guards and providers are |
| 5 | `config/areas.php` + `UseAreaSession` middleware + per-area session tables | Sessions, cookies, middleware order |
| 6 | Admin login / logout touching only its own session | `logout` vs `invalidate`, session regeneration |
| 7 | Prove it: two cookies in DevTools + feature tests | Testing auth |
| 8 | Permissions enum, seeder, roles, policies, `Gate::before` | Authorization done right |
| 9 | Admin user + role management screens | CRUD with Form Requests, policies, pagination |
| 10 | Activity log | Model events / auditing |
| 11 | `docs/adding-an-area.md` + `docs/creating-a-theme.md` | Your checklists |
| 12 | **You build the `client` area** from the checklist; I review | Proof you've got it |

## Credits (`CREDITS.md`)

- rappasoft/laravel-boilerplate — Anthony Rappa (MIT) — original inspiration
- Gradient Able — CodedThemes (MIT) — admin template
- Troy — original Bee-Force admin layout design

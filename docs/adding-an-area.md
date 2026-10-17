# Adding an area

> Verified: following this guide step by step on a fresh copy produced a working `partner` area
> (5 new tests passing alongside the existing suite, two cookies, independent logout).

An **area** is a part of the app with its own kind of account, its own login and its
own session. The boilerplate ships with two:

| Area | URLs | Accounts | Session cookie | Sessions table |
|---|---|---|---|---|
| `web` | `/…` | `users` | `{app}-session` | `sessions` |
| `admin` | `/admin/…` | `admins` | `{app}-admin-session` | `admin_sessions` |

This guide adds a third one. The example is a **`partner`** area (`/partner/…`, a `partners`
table). For your own area, replace `partner` / `Partner` / `partners` everywhere.

> **Naming rule:** use the **same word** for the area, the guard and the route-name prefix
> (`partner`, `auth:partner`, `partner.login`). Plural for the table and the provider
> (`partners`). Things only stay readable if those line up.

---

## How it fits together (read once)

```
config/areas.php ─┬─ guard 'partner' ──► config/auth.php guard ──► provider 'partners' ──► App\Models\Partner
                  ├─ session        ──► own cookie + own table (partner_sessions)
                  ├─ theme          ──► resources/themes/{name}
                  └─ routes         ──► routes/partner/*.php        (login required)
                                        routes/partner/public/*.php (no login: the login page)
```

`bootstrap/app.php` loops over `config/areas.php`. For every area with a `routes` folder it adds:

- the URL prefix (`/partner`) and route-name prefix (`partner.`),
- the `area:partner` middleware → **own session cookie/table, `auth()` = partner guard, own theme**,
- `auth:partner` + `active` on everything **except** `routes/partner/public/`.

So you never touch `bootstrap/app.php`. You add files and config.

---

## Checklist

### 1. Model, migration, factory

```bash
php artisan make:model Partner -mf
```

**Migration** (`database/migrations/…_create_partners_table.php`). Accounts table plus the area's
sessions table (same shape as `sessions`):

```php
public function up(): void
{
    Schema::create('partners', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->boolean('active')->default(true);   // optional: lets you deactivate accounts
        $table->rememberToken();
        $table->timestamps();
    });

    // Sessions of the partner area. user_id holds a partners.id.
    Schema::create('partner_sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });
}

public function down(): void
{
    Schema::dropIfExists('partner_sessions');
    Schema::dropIfExists('partners');
}
```

**Model** (`app/Models/Partner.php`). It must extend `Authenticatable` so it can log in:

```php
namespace App\Models;

use Database\Factories\PartnerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'active'])]
#[Hidden(['password', 'remember_token'])]
class Partner extends Authenticatable
{
    /** @use HasFactory<PartnerFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }
}
```

**Factory** (`database/factories/PartnerFactory.php`). Copy `AdminFactory`, rename the class and the
`@extends Factory<Partner>` line, and drop the fields your table doesn't have.

```bash
php artisan migrate
```

### 2. Guard and provider: `config/auth.php`

```php
'guards' => [
    // ...
    'partner' => [
        'driver' => 'session',
        'provider' => 'partners',
    ],
],

'providers' => [
    // ...
    'partners' => [
        'driver' => 'eloquent',
        'model' => App\Models\Partner::class,
    ],
],
```

*Guard* = how a login is remembered (the session). *Provider* = where accounts are looked up
(the `partners` table through the `Partner` model).

### 3. The area: `config/areas.php`

```php
'partner' => [
    'guard' => 'partner',
    'theme' => env('THEME_PARTNER', 'gradient-able'),
    'prefix' => 'partner',
    'routes' => 'routes/partner',
    'home' => 'partner.dashboard',
    'login' => 'partner.login',
    'account' => null,                 // route name of a "my account" page, once you add one
    'logout' => 'partner.logout',
    'session' => [
        'cookie' => env('PARTNER_SESSION_COOKIE', "{$app}-partner-session"),
        'table' => 'partner_sessions',
        'lifetime' => (int) env('PARTNER_SESSION_LIFETIME', 120),   // minutes
        'expire_on_close' => false,
    ],
],
```

`home`, `login`, `account`, `logout` are **route names**. The theme layouts read them
(`$theme->url('logout')`), so the header menu and redirects work without editing the theme.

### 4. Login controller

Copy `app/Http/Controllers/Admin/Auth/LoginController.php` to
`app/Http/Controllers/Partner/Auth/LoginController.php`, then:

- change the namespace to `App\Http\Controllers\Partner\Auth`,
- replace every `'admin'` guard with `'partner'`, and every `admin.` route/view name with `partner.`,
- delete the `last_login_at` / `last_login_ip` lines (unless your table has those columns),
- update the comments, which still say "admin".

The important lines, for reference:

```php
public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate('partner');        // ← which guard to log in on
    $request->session()->regenerate();

    return redirect()->intended(route('partner.dashboard'));
}

public function destroy(Request $request): RedirectResponse
{
    Auth::guard('partner')->logout();
    $request->session()->invalidate();        // only the partner session: it has its own cookie/table
    $request->session()->regenerateToken();

    return redirect()->route('partner.login');
}
```

`LoginRequest` already handles validation, rate limiting and deactivated accounts for any guard.

### 5. Routes: `routes/partner/`

Every file here gets `/partner`, `partner.` names and the area middleware automatically.

```php
// routes/partner/public/login.php   (no login required)
use App\Http\Controllers\Partner\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:partner')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});

Route::get('logout', [LoginController::class, 'confirm'])->name('logout.confirm');
```

```php
// routes/partner/account.php        (logged-in partners only, added automatically)
use App\Http\Controllers\Partner\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
```

```php
// routes/partner/dashboard.php
use Illuminate\Support\Facades\Route;

Route::view('/', 'partner.dashboard')->name('dashboard');
```

> Give **every** route a name. An unnamed route inside the area becomes just `partner.`, and two
> of those break `php artisan route:cache`.

### 6. Views: `resources/views/partner/`

Pages only use theme components (`<x-ui::…>`). Pick a layout:
`layouts.admin` (sidebar), `layouts.user` (top bar) or `layouts.guest` (centered card, for login).

```blade
{{-- resources/views/partner/auth/login.blade.php --}}
<x-ui::layouts.guest title="Partner login">
    <div class="text-center mb-4">
        <h4 class="mb-1">Partner portal</h4>
    </div>
    <form method="POST" action="{{ route('partner.login') }}">
        @csrf
        <x-ui::input name="email" type="email" label="Email" autocomplete="username" required autofocus />
        <x-ui::input name="password" type="password" label="Password" autocomplete="current-password" required />
        <div class="d-grid">
            <x-ui::button icon="sign-in">Log in</x-ui::button>
        </div>
    </form>
</x-ui::layouts.guest>
```

```blade
{{-- resources/views/partner/dashboard.blade.php --}}
<x-ui::layouts.user title="Dashboard">
    <x-ui::card title="Welcome, {{ auth()->user()->name }}">
        Partner content goes here.
    </x-ui::card>
</x-ui::layouts.user>
```

`auth()->user()` is a `Partner` here: the area made `partner` the default guard.

### 7. Menu: `config/menu.php`

The layout shows the menu of the current area:

```php
'partner' => [
    ['label' => 'Dashboard', 'route' => 'partner.dashboard', 'icon' => 'gauge'],
],
```

Icons: names from https://phosphoricons.com without the `ph-` prefix.

### 8. A first account (seeder)

```php
// database/seeders/PartnerSeeder.php
Partner::firstOrCreate(['email' => 'partner@example.com'], ['name' => 'Demo Partner', 'password' => 'password']);
```

Add it to `DatabaseSeeder::run()` (`$this->call([... PartnerSeeder::class])`), then run
`php artisan db:seed --class=PartnerSeeder`. The seeders only *add*, so this is safe on a dev database.
**Never** run `migrate:fresh` on a database you care about. It deletes everything.

### 9. Tests: `tests/Feature/Auth/PartnerAuthenticationTest.php`

Copy `tests/Feature/Auth/AdminAuthenticationTest.php` and rename `Admin` → `Partner`,
`'admin'` → `'partner'`, `/admin` → `/partner`. Delete the tests for features your area
doesn't have (last login, `/admin/components`). Keep these at minimum:

- guests are sent to `/partner/login`,
- partners can log in and out,
- **user and admin accounts can't log in here**,
- a deactivated partner can't log in (if you kept the `active` column).

Then the test that proves the point of an area: user and partner logged in at once, logging out of
one leaves the other alone. Note the first three lines. Tests normally keep sessions in memory;
this one needs real session tables:

```php
public function test_logging_out_of_partner_keeps_the_user_logged_in(): void
{
    config(['session.driver' => 'database']);
    $this->app['session']->forgetDrivers();
    $this->app->forgetInstance('session.store');

    $user = User::factory()->create();
    $partner = Partner::factory()->create();
    $webCookie = config('session.cookie');
    $partnerCookie = config('areas.areas.partner.session.cookie');

    $webSession = $this->post('/login', ['email' => $user->email, 'password' => 'password'])->getCookie($webCookie)->getValue();
    $partnerSession = $this->post('/partner/login', ['email' => $partner->email, 'password' => 'password'])->getCookie($partnerCookie)->getValue();

    // The browser sends both cookies, like a real one would.
    $this->withCookies([$webCookie => $webSession, $partnerCookie => $partnerSession])->post('/partner/logout');

    $this->assertDatabaseMissing('partner_sessions', ['id' => $partnerSession]);           // partner session gone
    $this->assertDatabaseHas('sessions', ['id' => $webSession, 'user_id' => $user->id]);   // user untouched
}
```

### 10. Check it

```bash
php artisan route:list --path=partner      # every route named partner.*, login under public
php artisan test                           # all green
```

In the browser: log in as a user at `/login` and as a partner at `/partner/login` in the **same**
window → DevTools → Application → Cookies shows `…-session` **and** `…-partner-session`.
Log out of one; the other stays logged in.

---

## Optional extras

| Want | How |
|---|---|
| Deactivate partners | Keep the `active` column. The `active` middleware and `LoginRequest` already handle it. |
| Forgot password | Add a `passwords.partners` broker in `config/auth.php` with its own token table, then copy `ForgotPasswordController` / `ResetPasswordController` using `Password::broker('partners')`. |
| "My account" page | Copy `Admin\AccountController` + its routes and view; set `'account' => 'partner.account.edit'` in `config/areas.php`. Use `current_password:partner` in validation. |
| Different look | Set `'theme' => 'my-theme'` (see `docs/creating-a-theme.md`). |
| Banner on every page | Create `resources/views/partner/notices.blade.php`; layouts include `{area}.notices` automatically. |
| Roles & permissions | Add `HasRoles` to the model and create roles with `guard_name = 'partner'`. Roles belong to one guard, so admin roles never apply here. |

## Common mistakes

| Symptom | Cause |
|---|---|
| Login "works" but the next page is logged out | The guard in `authenticate('…')` or `Auth::guard('…')` doesn't match the area's guard |
| Logging out of one area logs out the other | The area has no `session` entry in `config/areas.php`, so it shares the default cookie |
| `Route [partner.login] not defined` | Route file not in `routes/partner/` (or the area has no `routes` key) |
| `route:cache` fails with duplicate name `partner.` | A route inside the area has no `->name(...)` |
| "Password is incorrect" on a correct password | Validation used `current_password` without `:partner` and checked the wrong guard |
| `There is no role named … for guard partner` | The role was created for another guard. Pass `guard_name` |

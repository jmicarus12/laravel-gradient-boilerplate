# Creating a theme

> Verified: copying `gradient-able` to a new folder and setting `THEME_ADMIN` switched only the
> admin area to it (users kept their theme), with cached views too.

A **theme** is a folder in `resources/themes/` that holds all template HTML, CSS and JS.
Pages in `resources/views` only use `<x-ui::…>` components, so the same pages render in any theme.
The boilerplate ships with `gradient-able` (Gradient Able by CodedThemes, MIT).

Each **area** picks its theme in `config/areas.php` (`'theme' => env('THEME_ADMIN', 'gradient-able')`),
so admin and users can even use different themes.

---

## What a theme must work with

Read this before picking a template. Pages use three things:

| Pages use | So a theme must |
|---|---|
| `<x-ui::…>` components (below) | provide every component, **same names and props** |
| Bootstrap 5 grid & utilities: `row`, `col-md-6`, `d-flex`, `gap-2`, `mb-3`, `text-muted`, `small`, `fw-semibold`, `text-end`… | be **Bootstrap 5-based** (or provide those classes) |
| Phosphor icons: `<i class="ph ph-gauge">`, and icon names in `config/menu.php` | load **Phosphor** (`@phosphor-icons/web`) |

**Any Bootstrap 5 admin template** (most ThemeForest and free templates) fits as is.
A Tailwind or other non-Bootstrap template also needs the grid/utility classes in the pages replaced.
That's a bigger job: the components alone don't cover it.

---

## How it's wired

```
<x-ui::card>  ──►  Blade namespace "ui" → "theme::components"            (ThemeServiceProvider)
              ──►  "theme::" → resources/themes/{area's theme}/            (ThemeManager::use, called
              ──►  resources/themes/gradient-able/components/card.blade.php   by the area middleware)
```

| File | Role |
|---|---|
| `config/areas.php` | which theme each area uses (`theme` key) |
| `app/Theme/ThemeManager.php` | points `theme::` at the active folder; builds menus; area URLs |
| `app/Providers/ThemeServiceProvider.php` | registers `<x-ui::…>`; shares `$theme` with every view |
| `config/menu.php` | menu items (the theme only *renders* them) |
| `vite.config.js` | builds every `resources/themes/*/scss/app.scss` and `js/app.js` automatically |

Inside any theme file, **`$theme`** is the `ThemeManager`:

| Call | Returns |
|---|---|
| `$theme->vite()` | the theme's Vite entries, for `@vite($theme->vite())` |
| `$theme->menu()` | main menu items of the current area |
| `$theme->pinnedMenu()` | menu items pinned to the bottom of the sidebar (may be empty) |
| `$theme->url('home' \| 'login' \| 'register' \| 'account' \| 'logout')` | that area route's URL, or `null` if the area has none |
| `$theme->area()` / `$theme->name()` | current area / theme name |

**Menu items** (from `$theme->menu()`) are arrays. Hidden items (no permission, route not yet defined)
are already removed:

```php
['caption' => 'System']                                        // section heading
['label' => 'Admins', 'url' => '…', 'icon' => 'user-gear',    // link (icon optional)
 'is_active' => true]
['label' => 'Access', 'url' => '#', 'icon' => 'lock-key',      // parent with a submenu
 'is_active' => false, 'children' => [ /* more links */ ]]
```

---

## The contract: files a theme provides

```
resources/themes/my-theme/
├── components/
│   ├── layouts/
│   │   ├── admin.blade.php      sidebar layout
│   │   ├── user.blade.php       top-bar layout
│   │   └── guest.blade.php      centered card (login, register, …)
│   ├── page-header.blade.php    card.blade.php        stat-card.blade.php   table.blade.php
│   ├── pagination.blade.php     button.blade.php      input.blade.php       select.blade.php
│   ├── chart.blade.php
│   ├── checkbox.blade.php       alert.blade.php       badge.blade.php       flash.blade.php
├── scss/app.scss                Vite entry (CSS)
├── js/app.js                    Vite entry (JS)
└── LICENSE                      the template's license, if it has one
```

Partials (`partials/…`) are the theme's own business. Organize them however you like.

### Layouts

| Component | Props | Must |
|---|---|---|
| `layouts.admin` | `title`, `breadcrumbs` (`['Label' => url\|null]`) | render `$theme->menu()` and `$theme->pinnedMenu()`; header with account link + logout form (`$theme->url('account')`, `$theme->url('logout')`) |
| `layouts.user` | `title`, `breadcrumbs` | render `$theme->menu()`; logged in → account + logout; guest → login/register links (`$theme->url(...)`, skip `null`s) |
| `layouts.guest` | `title` | centered content, no menu |

**Every layout must:**
- output `<meta name="csrf-token" content="{{ csrf_token() }}">` and `@vite($theme->vite())` in `<head>`,
- output `@stack('styles')` just before `</head>` and `@stack('scripts')` just before `</body>`,
  so pages can add their own CSS/JS with `@push('styles')` / `@push('scripts')`,
- render `<x-ui::flash />` above the page content,
- render `@includeIf($theme->area().'.notices')` above the page content (app banners, e.g. "verify your email"),
- render `{{ $slot }}` (the page).

**Logout is a POST form** with `@csrf`, never a plain link.

### Components

Copy the props **exactly**. Pages pass these by name.

| Component | Props | Notes |
|---|---|---|
| `page-header` | `title`, `breadcrumbs = []` | |
| `card` | `title = null`, `flush = false` | slots: default, `actions` (header right), `footer`. `flush` = no body padding (tables) |
| `stat-card` | `label`, `value`, `icon = null`, `variant = 'primary'`, `note = null`, `noteValue = null` | colored summary tile |
| `table` | `headers = []` | default slot = `<tr>` rows |
| `pagination` | `paginator` | render nothing when there's only one page |
| `chart` | `type = 'line'` (`line`, `area`, `bar`, `donut`, `pie`), `series = []`, `labels = []`, `height = 300` | `series`/`labels` use ApexCharts' shape (see the component's comment). Any chart library works; redraw on light/dark switch |
| `button` | `variant = 'primary'`, `size = null`, `icon = null`, `href = null`, `type = 'submit'`, `outline = false` | `href` → `<a>`, else `<button>` |
| `input` | `name`, `label = null`, `type = 'text'`, `value = null`, `help = null`, `bag = 'default'` | shows `old()` (not for passwords) and the error from `$errors->getBag($bag)` |
| `select` | `name`, `label = null`, `options = []` (`value => label`), `value = null`, `bag = 'default'` | same error/old() handling |
| `checkbox` | `name`, `label`, `checked = false`, `value = '1'`, `id = null`, `help = null` | for `name[]` lists the page decides `checked` |
| `alert` | `type = 'primary'`, `dismissible = true` | |
| `badge` | `variant = 'primary'` | |
| `flash` | — | shows session `success`, `status`, `error`, `warning` as alerts |

Variants are Bootstrap's: `primary`, `secondary`, `success`, `danger`, `warning`, `info`.
Merge extra attributes with `{{ $attributes->class([...]) }}` so pages can add classes.

---

## Steps

1. **Pick a Bootstrap 5 template** and check its license allows your use. Keep its `LICENSE` in the theme folder.
2. **Copy the reference theme:**
   ```bash
   cp -R resources/themes/gradient-able resources/themes/my-theme
   ```
   You now have a working theme to change piece by piece. Nothing ever breaks completely.
3. **Assets.** Replace `scss/` and `js/` with the template's. `scss/app.scss` and `js/app.js` must stay
   the entry points. Add template npm packages with `npm install -D …`. Make sure Bootstrap's JS (dropdowns)
   and Phosphor icons are loaded. Prefer bundling fonts and icons over CDN links.
4. **Layouts.** Rebuild `components/layouts/*` with the template's page skeleton (sidebar, header,
   content wrapper). Keep the "every layout must" list above.
5. **Components.** Rewrite each component's markup with the template's version (its card, its buttons…).
   Keep every prop.
6. **Switch an area to it** in `.env`, and build:
   ```bash
   THEME_ADMIN=my-theme        # or THEME_WEB=…   (read by config/areas.php)
   npm run build
   ```
7. **Check it** (next section). Then delete whatever you didn't change from the copy.

### Restyling instead of replacing

To only change colors, fonts or the header of `gradient-able`, edit
`resources/themes/gradient-able/scss/_brand.scss` and run `npm run build`. Nothing else is needed.
`scss/vendor/` is the upstream template, and local edits there are marked `[boilerplate]`.

---

## Check your theme

| Check | Where |
|---|---|
| Every component (charts too), light **and** dark mode | `/admin/components` (UI Components in the admin sidebar) |
| Icons all show (no empty squares) | `/admin/icons` |
| Sidebar, pinned System menu, submenu open on active page | `/admin`, `/admin/admins` |
| Top bar logged out (login/register) and logged in (account/logout) | `/` |
| Forms with validation errors and the error-bag form | `/register` (submit empty), `/profile` (delete account with a wrong password) |
| Guest layout | `/login`, `/admin/login`, `/forgot-password` |
| Tables, pagination, filters | `/admin/admins`, `/admin/activity` |
| Phone width (≈ 400px): menu toggle, no sideways scroll | any page |

```bash
php artisan test     # ThemeTest and the page tests render every layout
```

Theme switching takes effect on the next request, even with `php artisan view:cache` active:
component names are resolved when the page renders, not when it's compiled.

## Common mistakes

| Symptom | Cause |
|---|---|
| `Unable to locate a class or view for component [ui::…]` | the theme is missing that component file |
| `Theme [my-theme] not found` | folder name ≠ the value in `.env` / `config/areas.php` |
| Page loads without styles | `npm run build` not run, or the entry isn't at `scss/app.scss` / `js/app.js` |
| Validation errors don't show | `input`/`select` don't read `$errors->getBag($bag)` |
| Success messages never appear | layout is missing `<x-ui::flash />` |
| Logout gives 405 / "page expired" | logout rendered as a link, or the form lacks `@csrf` |
| Icons are empty squares | Phosphor not loaded in `js/app.js` |

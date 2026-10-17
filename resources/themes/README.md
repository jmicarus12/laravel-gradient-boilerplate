# Themes

A theme is a folder here that holds **all** template HTML and CSS. Pages in
`resources/views` only use `<x-ui::...>` components, so they work with any theme.

| Theme | |
|---|---|
| `gradient-able` | the default: dark blue and white, light/dark mode |
| `gradient-able-sunset-orange` | example copy with an orange/charcoal palette (`_brand.scss`, `_custom.scss`, `_dark.scss` differ). Try it: `THEME_ADMIN=gradient-able-sunset-orange` in `.env` |

## How it's wired

| Piece | File |
|---|---|
| Which theme each area uses | `config/areas.php` (`theme` key) |
| `<x-ui::card>` → `resources/themes/{active}/components/card.blade.php` | `app/Providers/ThemeServiceProvider.php` |
| Picking the area for a route group (`->middleware('area:admin')`) | `app/Http/Middleware/UseArea.php` → `app/Areas/AreaManager.php` |
| Switching folders, Vite entries, menus | `app/Theme/ThemeManager.php` |
| Sidebar / top-bar menu items | `config/menu.php` |
| Vite picks up every theme's `scss/app.scss` + `js/app.js` | `vite.config.js` |

Inside theme Blade files, `$theme` is the `ThemeManager` (`$theme->vite()`, `$theme->menu()`).

## Making or changing a theme

- **Full guide:** [`docs/creating-a-theme.md`](../../docs/creating-a-theme.md): the component contract, steps and a checklist.
- **Only new colors/fonts:** edit `gradient-able/scss/_brand.scss` and run `npm run build`.

`gradient-able/scss/vendor/` is the upstream template Sass (Gradient Able by CodedThemes, MIT).
Local edits there are marked `[boilerplate]` so the template can be updated later.

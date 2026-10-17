<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>

{{-- Apply light/dark before the first paint (see js/theme-mode.js) --}}
<script>
    (function () {
        var pref = 'auto';
        try { pref = localStorage.getItem('theme-mode') || 'auto'; } catch (e) {}
        var dark = pref === 'dark' || (pref === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.dataset.themePref = pref;
        document.documentElement.dataset.bsTheme = dark ? 'dark' : 'light';
    })();
</script>

@vite($theme->vite())

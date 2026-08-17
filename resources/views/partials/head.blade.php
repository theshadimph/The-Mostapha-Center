<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

{{-- Material Symbols is an icon font, not a text typeface bunny.net mirrors, so it's loaded from Google Fonts directly like every Stitch mockup does. --}}
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])

{{--
    Livewire only auto-injects its script (which bundles Alpine.js — this
    app has no separate Alpine import, see resources/js/app.js) on pages
    that render an actual Livewire component. Plain Blade pages (dashboard,
    the entire public site, auth pages) need it declared explicitly, or
    every x-data/x-show/x-cloak on them silently does nothing.
--}}
@livewireStyles

{{-- Applies the persisted (or OS-default, on first visit) appearance before first paint, avoiding a light/dark flash. Alpine.store('appearance', ...) in resources/js/appearance.js takes over from here. --}}
<script>
    (function () {
        var stored = localStorage.getItem('appearance');
        var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', stored ? stored === 'dark' : prefersDark);
    })();
</script>

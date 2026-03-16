<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CPROMED PERU') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
    <script>
        (function() {
            function applyTheme() {
                var html = document.documentElement, body = document.body;
                if (!body) return;
                var theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                if (theme === 'dark') { html.classList.add('dark'); body.classList.add('dark', 'bg-gray-900'); }
                else { html.classList.remove('dark'); body.classList.remove('dark', 'bg-gray-900'); }
            }
            if (document.body) applyTheme(); else document.addEventListener('DOMContentLoaded', applyTheme);
        })();
    </script>
</head>
<body class="antialiased">
    {{-- Preloader (mismo comportamiento que layouts Blade) --}}
    <div id="app-preloader" class="fixed left-0 top-0 z-[999999] flex h-screen w-screen items-center justify-center bg-white dark:bg-black transition-opacity duration-300" role="status" aria-label="Cargando">
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent" aria-hidden="true"></div>
    </div>
    @inertia
    <script>
        (function() {
            function hidePreloader() {
                var el = document.getElementById('app-preloader');
                if (el) { el.style.opacity = '0'; el.style.pointerEvents = 'none'; setTimeout(function() { el.remove(); }, 350); }
            }
            if (document.readyState === 'complete') setTimeout(hidePreloader, 350);
            else window.addEventListener('load', function() { setTimeout(hidePreloader, 350); });
        })();
    </script>
</body>
</html>

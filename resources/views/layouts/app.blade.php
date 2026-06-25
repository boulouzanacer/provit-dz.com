<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="themeSwitcher()"
      x-init="init()"
      :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            darkMode: 'class',
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        function themeSwitcher() {
            return {
                dark: false,
                init() {
                    const stored = localStorage.getItem('theme');
                    if (stored === 'dark') {
                        this.dark = true;
                        return;
                    }
                    if (stored === 'light') {
                        this.dark = false;
                        return;
                    }
                    this.dark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                },
                toggle() {
                    this.dark = !this.dark;
                    localStorage.setItem('theme', this.dark ? 'dark' : 'light');
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
    <button type="button"
            class="fixed top-4 right-4 z-50 inline-flex items-center gap-2 rounded-xl bg-white/80 px-4 py-2 text-sm font-semibold shadow-lg backdrop-blur hover:bg-white dark:bg-slate-900/70 dark:hover:bg-slate-900"
            @click="toggle()">
        <i class="fa-solid" :class="dark ? 'fa-sun' : 'fa-moon'"></i>
        <span x-text="dark ? 'Clair' : 'Sombre'"></span>
    </button>

    @yield('content')
</body>
</html>

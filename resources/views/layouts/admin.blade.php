<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="adminTheme()" x-init="init()" :class="{ 'dark': dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Administration' }} - {{ config('app.name', 'Pro-Vit') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>window.tailwind = window.tailwind || {}; window.tailwind.config = { darkMode: 'class' };</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root{--admin-primary:#1E6FD9;--admin-bg:#17172e;--admin-card:#232344;}
        html:not(.dark){--admin-bg:#f8fafc;--admin-card:#ffffff;}
        html,body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;}
        html:not(.dark) .text-white\/80{color:rgb(30 41 59 / 1)}
        html:not(.dark) .text-white\/70{color:rgb(71 85 105 / 1)}
        html:not(.dark) .text-white\/60{color:rgb(100 116 139 / 1)}
        html:not(.dark) .text-white\/50{color:rgb(100 116 139 / 1)}
        html:not(.dark) .border-white\/10{border-color:rgb(226 232 240 / 1)}
        html:not(.dark) .divide-white\/10>:not([hidden])~:not([hidden]){border-color:rgb(226 232 240 / 1)}
        html:not(.dark) .bg-white\/10{background-color:rgb(241 245 249 / 1)}
        html:not(.dark) .hover\:bg-white\/10:hover{background-color:rgb(241 245 249 / 1)}
    </style>
    <script>
        function adminTheme(){
            return {
                dark: true,
                profileOpen: false,
                init(){
                    const stored = localStorage.getItem('admin_theme');
                    this.dark = stored ? stored === 'dark' : true;
                },
                toggleTheme(){
                    this.dark = !this.dark;
                    localStorage.setItem('admin_theme', this.dark ? 'dark' : 'light');
                }
            }
        }
    </script>
</head>
<body class="min-h-screen text-slate-100" :class="dark ? 'bg-[var(--admin-bg)]' : 'bg-slate-100 text-slate-900'">
@php($admin = \App\Models\Admin::find(session('admin_id')))
<div class="flex min-h-screen">
    <aside class="fixed inset-y-0 left-0 w-[255px] border-r bg-[var(--admin-bg)]" :class="dark ? 'border-white/10' : 'border-slate-200'">
        <div class="h-16 px-5 flex items-center gap-3 border-b" :class="dark ? 'border-white/10' : 'border-slate-200'">
            <div class="h-10 w-10 rounded-2xl flex items-center justify-center font-extrabold text-white" style="background:linear-gradient(135deg,var(--admin-primary),#0f3b8c)">PV</div>
            <div>
                <div class="font-extrabold tracking-wide">Pro-Vit</div>
                <div class="text-xs" :class="dark ? 'text-white/60' : 'text-slate-500'">Administration</div>
            </div>
        </div>
        <nav class="px-4 py-4 space-y-1 text-sm">
            <a href="{{ url('/admin/dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 {{ request()->is('admin/dashboard') ? 'bg-white/10 border border-[var(--admin-primary)]' : '' }}" :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'"><i class="fa-solid fa-chart-line w-5 text-[var(--admin-primary)]"></i><span>Dashboard</span></a>
            <a href="{{ url('/admin/distributeurs') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 {{ request()->is('admin/distributeurs*') ? 'bg-white/10 border border-[var(--admin-primary)]' : '' }}" :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'"><i class="fa-solid fa-store w-5 text-[var(--admin-primary)]"></i><span>Distributeurs</span></a>
            <a href="{{ url('/admin/clients') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 {{ request()->is('admin/clients*') ? 'bg-white/10 border border-[var(--admin-primary)]' : '' }}" :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'"><i class="fa-solid fa-users w-5 text-[var(--admin-primary)]"></i><span>Clients</span></a>
            <a href="{{ url('/admin/categories') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 {{ request()->is('admin/categories*') ? 'bg-white/10 border border-[var(--admin-primary)]' : '' }}" :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'"><i class="fa-solid fa-tags w-5 text-[var(--admin-primary)]"></i><span>Categories</span></a>
            <a href="{{ url('/admin/produits') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 {{ request()->is('admin/produits*') ? 'bg-white/10 border border-[var(--admin-primary)]' : '' }}" :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'"><i class="fa-solid fa-boxes-stacked w-5 text-[var(--admin-primary)]"></i><span>Produits</span></a>
            <a href="{{ url('/admin/commandes') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 {{ request()->is('admin/commandes*') ? 'bg-white/10 border border-[var(--admin-primary)]' : '' }}" :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'"><i class="fa-solid fa-cart-shopping w-5 text-[var(--admin-primary)]"></i><span>Commandes</span></a>
            <a href="{{ url('/admin/parametres') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 {{ request()->is('admin/parametres*') ? 'bg-white/10 border border-[var(--admin-primary)]' : '' }}" :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'"><i class="fa-solid fa-gear w-5 text-[var(--admin-primary)]"></i><span>Parametres</span></a>
            <form method="POST" action="{{ url('/admin/logout') }}" class="pt-2">@csrf<button type="submit" class="w-full flex items-center gap-3 rounded-xl px-4 py-3 text-left" :class="dark ? 'hover:bg-white/10' : 'hover:bg-slate-100'"><i class="fa-solid fa-right-from-bracket w-5 text-red-300"></i><span>Deconnexion</span></button></form>
        </nav>
    </aside>
    <div class="flex-1 ml-[255px]">
        <header class="sticky top-0 z-40 h-16 px-6 flex items-center justify-between border-b border-white/10 backdrop-blur" :class="dark ? 'bg-[color:rgba(23,23,46,0.86)]' : 'bg-white/80 border-slate-200'">
            <div class="font-extrabold tracking-wide text-lg">{{ $title ?? 'Administration' }}</div>
            <div class="flex items-center gap-4">
                <button type="button" @click="toggleTheme()" class="rounded-xl px-3 py-2 border border-white/10 hover:bg-white/10 text-sm font-semibold" :class="dark ? '' : 'border-slate-200 hover:bg-slate-100'"><i class="fa-solid" :class="dark ? 'fa-sun' : 'fa-moon'"></i></button>
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold">{{ trim(($admin?->prenom ?? 'Admin').' '.($admin?->nom ?? '')) }}</div>
                    <div class="text-xs text-white/60">Admin</div>
                </div>
            </div>
        </header>
        <main class="p-6 space-y-4">
            @if(session('success'))<div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/15 px-4 py-3 text-emerald-200">{{ session('success') }}</div>@endif
            @if(session('info'))<div class="rounded-2xl border border-sky-400/20 bg-sky-500/15 px-4 py-3 text-sky-200">{{ session('info') }}</div>@endif
            @if(session('error'))<div class="rounded-2xl border border-red-400/20 bg-red-500/15 px-4 py-3 text-red-200">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="rounded-2xl border border-red-400/20 bg-red-500/15 px-4 py-3 text-red-200">{{ $errors->first() }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
@include('partials.delete-confirm-modal')
</body>
</html>

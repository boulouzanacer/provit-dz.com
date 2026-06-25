<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Pro-Vit' }} - {{ config('app.name', 'Pro-Vit') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>window.tailwind = window.tailwind || {}; window.tailwind.config = { darkMode: 'class' };</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>:root{--store-primary:#1E6FD9;--store-bg:#f8fafc;--store-card:#ffffff}html,body{font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}</style>
</head>
<body class="min-h-screen bg-[var(--store-bg)] text-slate-900">
@php($cartCount = count(($cartSummary['items'] ?? [])))
<div class="min-h-screen flex flex-col">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 py-3 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <div class="h-11 w-11 rounded-2xl flex items-center justify-center font-extrabold text-white" style="background:linear-gradient(135deg,var(--store-primary),#0f3b8c)">PV</div>
                    <div><div class="font-extrabold tracking-wide">Pro-Vit</div><div class="text-xs text-slate-500">Produits detergents</div></div>
                </a>
                <a href="{{ url('/panier') }}" class="lg:hidden inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold"><i class="fa-solid fa-cart-shopping text-[var(--store-primary)]"></i><span>{{ $cartCount }}</span></a>
            </div>
            <div class="flex-1 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-end">
                @if(($distributors ?? collect())->count() > 0)
                    <form method="POST" action="{{ url('/selection-distributeur') }}" class="flex-1 lg:max-w-sm">@csrf
                        <div class="relative">
                            <i class="fa-solid fa-store absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <select name="id_frs" class="w-full rounded-2xl border border-slate-200 bg-white px-11 py-3 outline-none focus:border-[var(--store-primary)]" onchange="this.form.submit()" @if(($client ?? null) && ($client->id_frs ?? null)) disabled @endif>
                                @foreach(($distributors ?? collect()) as $d)
                                    <option value="{{ $d->id }}" @selected((int)($selectedFrsId ?? 0) === (int)$d->id)>{{ $d->nom_frs }}</option>
                                @endforeach
                            </select>
                            @if(($client ?? null) && ($client->id_frs ?? null))<input type="hidden" name="id_frs" value="{{ $client->id_frs }}">@endif
                        </div>
                    </form>
                @endif
                <div class="flex items-center gap-2">
                    <a href="{{ url('/panier') }}" class="hidden lg:inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold hover:bg-slate-50"><i class="fa-solid fa-cart-shopping text-[var(--store-primary)]"></i><span>Panier</span><span class="inline-flex min-w-[22px] justify-center rounded-full bg-slate-100 px-1.5 py-0.5 text-xs">{{ $cartCount }}</span></a>
                    @if(($client ?? null))
                        <a href="{{ url('/profil') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold hover:bg-slate-50"><i class="fa-solid fa-user text-[var(--store-primary)]"></i><span>Mon Compte</span></a>
                        <a href="{{ url('/mes-commandes') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold hover:bg-slate-50"><i class="fa-solid fa-receipt text-[var(--store-primary)]"></i><span>Mes Commandes</span></a>
                        <form method="POST" action="{{ url('/logout') }}">@csrf<button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold hover:bg-slate-50"><i class="fa-solid fa-right-from-bracket text-red-600"></i><span>Deconnexion</span></button></form>
                    @else
                        <a href="{{ url('/login') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold hover:bg-slate-50"><i class="fa-solid fa-user text-[var(--store-primary)]"></i><span>Connexion</span></a>
                        <a href="{{ url('/register') }}" class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-bold text-white" style="background:linear-gradient(135deg,var(--store-primary),#0f3b8c)"><i class="fa-solid fa-user-plus"></i><span>Inscription</span></a>
                    @endif
                </div>
            </div>
        </div>
    </header>
    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 py-6 space-y-4">
            @if(session('success'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('success') }}</div>@endif
            @if(session('info'))<div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sky-800">{{ session('info') }}</div>@endif
            @if(session('error'))<div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ $errors->first() }}</div>@endif
            @yield('content')
        </div>
    </main>
    <footer class="border-t border-slate-200 bg-white"><div class="max-w-7xl mx-auto px-4 py-6 text-sm text-slate-500">&copy; {{ date('Y') }} Pro-Vit</div></footer>
</div>
</body>
</html>

@extends('store.layout')
@section('content')
<div class="mx-auto max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
    <div class="text-center"><div class="mx-auto h-14 w-14 rounded-2xl flex items-center justify-center font-extrabold text-white" style="background:linear-gradient(135deg,#1E6FD9,#0f3b8c)">PV</div><div class="mt-4 text-2xl font-extrabold">Connexion client</div><div class="text-sm text-slate-500">Connectez-vous pour commander</div></div>
    <form method="POST" action="{{ url('/login') }}" class="mt-6 space-y-4">@csrf<div><label class="mb-2 block text-sm font-semibold">Email</label><input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required></div><div><label class="mb-2 block text-sm font-semibold">Mot de passe</label><input type="password" name="password" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required></div><button class="w-full rounded-2xl px-4 py-3 text-sm font-extrabold text-white" style="background:linear-gradient(135deg,#1E6FD9,#0f3b8c)">Connexion</button></form>
</div>
@endsection

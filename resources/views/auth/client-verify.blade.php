@extends('store.layout')
@section('content')
<div class="mx-auto max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
    <div class="text-center">
        <div class="mx-auto h-14 w-14 rounded-2xl flex items-center justify-center font-extrabold text-white" style="background:linear-gradient(135deg,#1E6FD9,#0f3b8c)">PV</div>
        <div class="mt-4 text-2xl font-extrabold">Confirmer mon email</div>
        <div class="mt-1 text-sm text-slate-500">Saisissez le code a 6 chiffres recu par email pour activer votre compte client.</div>
    </div>

    <form method="POST" action="{{ url('/verify-email') }}" class="mt-8 space-y-4">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-semibold">Email</label>
            <input type="email" name="email" value="{{ $email }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold">Code de confirmation</label>
            <input type="text" name="code" inputmode="numeric" maxlength="6" placeholder="000000" class="w-full rounded-2xl border border-slate-200 px-4 py-3 tracking-[0.45em] text-center text-xl font-extrabold" required>
        </div>
        <button class="w-full rounded-2xl px-4 py-3 text-sm font-extrabold text-white" style="background:linear-gradient(135deg,#1E6FD9,#0f3b8c)">Valider le code</button>
    </form>

    <form method="POST" action="{{ url('/verify-email/resend') }}" class="mt-4">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <button class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Renvoyer un code</button>
    </form>

    <div class="mt-4 text-center text-sm text-slate-500">
        Vous avez deja confirme votre email ?
        <a href="{{ url('/login') }}" class="font-semibold text-[var(--store-primary)]">Se connecter</a>
    </div>
</div>
@endsection

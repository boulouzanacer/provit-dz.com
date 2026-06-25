@extends('store.layout')
@section('content')
<div class="mx-auto max-w-2xl rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
    <div class="text-center"><div class="text-2xl font-extrabold">Creer un compte</div><div class="mt-1 text-sm text-slate-500">Choisissez votre distributeur le plus proche pour pouvoir commander.</div></div>
    <form method="POST" action="{{ url('/register') }}" class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">@csrf
        <div><label class="mb-2 block text-sm font-semibold">Nom</label><input type="text" name="nom" value="{{ old('nom') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required></div>
        <div><label class="mb-2 block text-sm font-semibold">Prenom</label><input type="text" name="prenom" value="{{ old('prenom') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required></div>
        <div><label class="mb-2 block text-sm font-semibold">Email</label><input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required></div>
        <div><label class="mb-2 block text-sm font-semibold">Telephone</label><input type="text" name="telephone" value="{{ old('telephone') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required></div>
        <div><label class="mb-2 block text-sm font-semibold">Mot de passe</label><input type="password" name="password" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required></div>
        <div><label class="mb-2 block text-sm font-semibold">Confirmation</label><input type="password" name="password_confirmation" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required></div>
        <div class="md:col-span-2"><label class="mb-2 block text-sm font-semibold">Adresse</label><textarea name="adresse" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3">{{ old('adresse') }}</textarea></div>
        <div class="md:col-span-2"><label class="mb-2 block text-sm font-semibold">Distributeur le plus proche</label><select name="id_frs" class="w-full rounded-2xl border border-slate-200 px-4 py-3" required>@foreach($distributors as $d)<option value="{{ $d->id }}" @selected(old('id_frs') == $d->id)>{{ $d->nom_frs }}{{ $d->ville ? ' - '.$d->ville : '' }}</option>@endforeach</select></div>
        <div class="md:col-span-2"><button class="w-full rounded-2xl px-4 py-3 text-sm font-extrabold text-white" style="background:linear-gradient(135deg,#1E6FD9,#0f3b8c)">Creer mon compte</button></div>
    </form>
</div>
@endsection

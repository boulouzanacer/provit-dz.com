@extends('layouts.admin')
@section('content')
<div class="max-w-4xl rounded-3xl border border-white/10 bg-[var(--admin-card)] p-6">
    <form method="POST" action="{{ url('/admin/parametres') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <div>
            <label class="mb-2 block text-sm font-semibold">Nom entreprise</label>
            <input name="company_name" value="{{ $settings['company_name'] }}" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="mb-2 block text-sm font-semibold">Telephone</label>
                <input name="company_phone" value="{{ $settings['company_phone'] }}" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold">Email</label>
                <input name="company_email" value="{{ $settings['company_email'] }}" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
            </div>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold">Adresse</label>
            <textarea name="company_address" rows="4" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">{{ $settings['company_address'] }}</textarea>
        </div>
        <div class="rounded-3xl border border-white/10 bg-black/20 p-5">
            <div class="mb-4">
                <div class="text-base font-extrabold text-white">Logo du site web</div>
                <div class="mt-1 text-sm text-white/60">Ce logo s affiche en haut a gauche du site public.</div>
            </div>
            <div class="flex flex-col gap-4 md:flex-row md:items-start">
                <div class="h-24 w-24 overflow-hidden rounded-3xl border border-white/10 bg-black/30">
                    @if(($settings['site_logo_url'] ?? '') !== '')
                        <img src="{{ $settings['site_logo_url'] }}" alt="Logo du site" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-white/35">
                            <i class="fa-regular fa-image text-2xl"></i>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <label class="mb-2 block text-sm font-semibold">Changer le logo</label>
                    <input type="file" name="site_logo" accept="image/*" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                    <div class="mt-2 text-xs text-white/55">Format image conseille. Le nouveau logo remplace automatiquement l ancien.</div>
                </div>
            </div>
        </div>
        <button class="rounded-2xl px-4 py-3 text-sm font-extrabold text-white" style="background:linear-gradient(135deg,#1E6FD9,#0f3b8c)">Enregistrer</button>
    </form>
</div>
@endsection

@extends('layouts.admin')
@section('content')
<div x-data="categoryManager()" class="space-y-4">
    <div class="flex items-center justify-between rounded-3xl border border-white/10 bg-[var(--admin-card)] p-5"><form class="flex-1 max-w-md"><input name="q" value="{{ $q }}" placeholder="Rechercher une categorie..." class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3"></form><button @click="openCreate()" class="inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-extrabold text-white" style="background:linear-gradient(135deg,#1E6FD9,#0f3b8c)"><i class="fa-solid fa-plus"></i><span>Nouveau</span></button></div>
    <div class="rounded-3xl border border-white/10 bg-[var(--admin-card)] p-5 overflow-x-auto"><table class="min-w-full text-sm"><thead class="text-white/60"><tr><th class="py-3 pr-4 text-left">Categorie</th><th class="py-3 pr-4 text-left">Produits</th><th class="py-3 pr-4 text-left">Statut</th><th class="py-3 text-right">Actions</th></tr></thead><tbody class="divide-y divide-white/10">@forelse($categories as $category)<tr><td class="py-3 pr-4"><div class="font-bold">{{ $category->nom }}</div><div class="text-xs text-white/60">{{ $category->description }}</div></td><td class="py-3 pr-4">{{ $category->produits_count }}</td><td class="py-3 pr-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ (int)$category->actif === 1 ? 'bg-emerald-500/15 text-emerald-200' : 'bg-red-500/15 text-red-200' }}">{{ (int)$category->actif === 1 ? 'Actif' : 'Inactif' }}</span></td><td class="py-3 text-right"><div class="inline-flex items-center gap-2"><button type="button" data-payload='@json(["id" => $category->id, "nom" => $category->nom, "description" => $category->description, "actif" => $category->actif])' @click="openEdit(JSON.parse($el.dataset.payload))" class="h-9 w-9 rounded-xl border border-white/10 hover:bg-white/10"><i class="fa-solid fa-pen"></i></button><form method="POST" action="{{ url('/admin/categories/'.$category->id) }}" class="inline" data-confirm-delete data-confirm-title="Supprimer cette categorie ?" data-confirm-message="La categorie {{ $category->nom }} sera retiree du catalogue administrateur." data-confirm-button="Oui, supprimer la categorie">@csrf @method('DELETE')<button class="h-9 w-9 rounded-xl border border-white/10 hover:bg-white/10 text-red-300"><i class="fa-solid fa-trash"></i></button></form></div></td></tr>@empty<tr><td colspan="4" class="py-8 text-center text-white/60">Aucune categorie</td></tr>@endforelse</tbody></table></div>
    <div x-show="open" x-transition class="fixed inset-0 z-50 flex justify-end bg-slate-950/60"><div class="h-full w-full max-w-xl overflow-y-auto border-l border-white/10 bg-[var(--admin-bg)] p-6"><div class="flex items-center justify-between"><div class="text-xl font-extrabold" x-text="editing ? 'Modifier categorie' : 'Nouvelle categorie'"></div><button @click="open=false" class="h-10 w-10 rounded-xl border border-white/10"><i class="fa-solid fa-xmark"></i></button></div><form class="mt-6 space-y-4" method="POST" :action="action">@csrf<template x-if="editing"><input type="hidden" name="_method" value="PUT"></template><div><label class="mb-2 block text-sm font-semibold">Nom</label><input x-model="form.nom" name="nom" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3" required></div><div><label class="mb-2 block text-sm font-semibold">Description</label><textarea x-model="form.description" name="description" rows="4" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3"></textarea></div><label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-3"><input type="checkbox" name="actif" value="1" x-model="form.actif"><span class="text-sm font-semibold">Active</span></label><button class="w-full rounded-2xl px-4 py-3 text-sm font-extrabold text-white" style="background:linear-gradient(135deg,#1E6FD9,#0f3b8c)">Enregistrer</button></form></div></div>
</div>
<script>
function categoryManager() {
    const baseAction = "{{ url('/admin/categories') }}";
    const emptyForm = {
        nom: '',
        description: '',
        actif: true,
    };

    return {
        open: false,
        editing: false,
        action: baseAction,
        form: { ...emptyForm },
        openCreate() {
            this.open = true;
            this.editing = false;
            this.action = baseAction;
            this.form = { ...emptyForm };
        },
        openEdit(category) {
            this.open = true;
            this.editing = true;
            this.action = `${baseAction}/${category.id}`;
            this.form = {
                nom: category.nom ?? '',
                description: category.description ?? '',
                actif: Number(category.actif) === 1,
            };
        },
    };
}
</script>
@endsection

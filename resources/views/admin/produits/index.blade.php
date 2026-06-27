@extends('layouts.admin')
@section('content')
<div x-data="productManager()" class="space-y-4">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between rounded-3xl border border-white/10 bg-[var(--admin-card)] p-5">
        <form class="flex-1 max-w-md"><input name="q" value="{{ $q }}" placeholder="Rechercher un produit..." class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3"></form>
        <button type="button" @click="create()" class="inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-extrabold text-white" style="background:linear-gradient(135deg,#1E6FD9,#0f3b8c)"><i class="fa-solid fa-plus"></i><span>Nouveau produit</span></button>
    </div>
    <div class="rounded-3xl border border-white/10 bg-[var(--admin-card)] p-5 overflow-x-auto">
        <table class="min-w-full text-sm"><thead class="text-white/60"><tr><th class="py-3 pr-4 text-left">Photo</th><th class="py-3 pr-4 text-left">Produit</th><th class="py-3 pr-4 text-left">Categorie</th><th class="py-3 pr-4 text-left">PV</th><th class="py-3 pr-4 text-left">Paliers</th><th class="py-3 pr-4 text-left">Statut</th><th class="py-3 text-right">Actions</th></tr></thead><tbody class="divide-y divide-white/10">@forelse($products as $product)@php($editPayload = base64_encode(json_encode(['id' => $product->id, 'code_barre' => $product->code_barre, 'designation' => $product->designation, 'description' => $product->description, 'pv' => $product->pv_1, 'stock' => $product->stock, 'id_category' => $product->id_category, 'actif' => (int) $product->actif, 'enable_tier_pricing' => (bool) $product->enable_tier_pricing, 'image_principale' => $product->image_principale, 'images' => $product->images->map(function ($image) { return ['id' => $image->id, 'url_thumbnail' => $image->url_thumbnail, 'url_principale' => $image->url_principale]; })->values()->all(), 'tiers' => $product->quantityPrices->map(function ($tier) { return ['quantity_min' => $tier->quantity_min, 'quantity_max' => $tier->quantity_max, 'price' => $tier->price]; })->values()->all()], JSON_UNESCAPED_UNICODE)))<tr><td class="py-3 pr-4"><div class="h-14 w-14 overflow-hidden rounded-2xl border border-white/10 bg-black/20">@if($product->image_principale)<img src="{{ $product->image_principale }}" alt="{{ $product->designation }}" class="h-full w-full object-cover">@else<div class="flex h-full w-full items-center justify-center text-white/35"><i class="fa-regular fa-image"></i></div>@endif</div></td><td class="py-3 pr-4"><div class="font-bold">{{ $product->designation }}</div><div class="text-xs text-white/60">Code barre: {{ $product->code_barre ?: '?' }}</div></td><td class="py-3 pr-4">{{ $product->category?->nom ?: '?' }}</td><td class="py-3 pr-4">{{ number_format($product->pv_1, 2, '.', ' ') }} DA</td><td class="py-3 pr-4">{{ $product->quantityPrices->count() }}</td><td class="py-3 pr-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ (int)$product->actif === 1 ? 'bg-emerald-500/15 text-emerald-200' : 'bg-red-500/15 text-red-200' }}">{{ (int)$product->actif === 1 ? 'Actif' : 'Inactif' }}</span></td><td class="py-3 text-right"><div class="inline-flex items-center gap-2"><button type="button" data-payload="{{ $editPayload }}" @click="edit(JSON.parse(atob($el.dataset.payload)))" class="h-9 w-9 rounded-xl border border-white/10 hover:bg-white/10"><i class="fa-solid fa-pen"></i></button><form method="POST" action="{{ url('/admin/produits/'.$product->id.'/toggle-actif') }}">@csrf<button class="h-9 w-9 rounded-xl border border-white/10 hover:bg-white/10"><i class="fa-solid {{ (int)$product->actif === 1 ? 'fa-toggle-on text-emerald-300' : 'fa-toggle-off text-red-300' }}"></i></button></form><form method="POST" action="{{ url('/admin/produits/'.$product->id) }}" class="inline" data-confirm-delete data-confirm-title="Supprimer ce produit ?" data-confirm-message="Le produit {{ $product->designation }} sera retire de l administration et ne sera plus disponible pour les distributeurs." data-confirm-button="Oui, supprimer le produit">@csrf @method('DELETE')<button class="h-9 w-9 rounded-xl border border-white/10 hover:bg-white/10 text-red-300"><i class="fa-solid fa-trash"></i></button></form></div></td></tr>@empty<tr><td colspan="7" class="py-8 text-center text-white/60">Aucun produit</td></tr>@endforelse</tbody></table>
    </div>
    <div x-show="open" x-transition class="fixed inset-0 z-50 flex justify-end bg-slate-950/60"><div class="h-full w-full max-w-2xl overflow-y-auto border-l border-white/10 bg-[var(--admin-bg)] p-6"><div class="flex items-center justify-between"><div class="text-xl font-extrabold" x-text="editing ? 'Modifier produit' : 'Nouveau produit'"></div><button @click="close()" class="h-10 w-10 rounded-xl border border-white/10"><i class="fa-solid fa-xmark"></i></button></div><form class="mt-6 space-y-4" method="POST" :action="action" enctype="multipart/form-data">@csrf<template x-if="editing"><input type="hidden" name="_method" value="PUT"></template><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><div><label class="mb-2 block text-sm font-semibold">Code barre</label><input x-model="form.code_barre" name="code_barre" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3"></div><div><label class="mb-2 block text-sm font-semibold">Categorie</label><select x-model="form.id_category" name="id_category" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3"><option value="">Choisir</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->nom }}</option>@endforeach</select></div></div><div><label class="mb-2 block text-sm font-semibold">Designation</label><input x-model="form.designation" name="designation" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3" required></div><div><label class="mb-2 block text-sm font-semibold">Description</label><textarea x-model="form.description" name="description" rows="4" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3"></textarea></div><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><div><label class="mb-2 block text-sm font-semibold">PV</label><input x-model="form.pv" type="number" step="0.01" name="pv" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3" required></div><div><label class="mb-2 block text-sm font-semibold">Stock par defaut</label><input x-model="form.stock" type="number" name="stock" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3"></div></div><div class="space-y-3"><label class="mb-2 block text-sm font-semibold">Images</label><div class="flex items-start gap-4 rounded-3xl border border-white/10 bg-black/20 p-4"><div class="h-24 w-24 overflow-hidden rounded-2xl border border-white/10 bg-black/30"><template x-if="previewUrl"><img :src="previewUrl" alt="Apercu produit" class="h-full w-full object-cover"></template><template x-if="!previewUrl"><div class="flex h-full w-full items-center justify-center text-white/35"><i class="fa-regular fa-image text-xl"></i></div></template></div><div class="flex-1"><div class="mb-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Apercu avant validation</div><input x-ref="imagesInput" @change="previewImages($event)" type="file" name="images[]" multiple class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3"><div class="mt-2 text-xs text-white/55">La premiere image selectionnee est affichee immediatement dans l apercu.</div></div></div><div class="rounded-3xl border border-white/10 bg-black/10 p-4" x-show="editing && form.images.length"><div class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Photos actuelles</div><div class="grid grid-cols-2 gap-3 sm:grid-cols-3"><template x-for="image in form.images" :key="image.id"><div class="relative overflow-hidden rounded-2xl border border-white/10 bg-black/30"><img :src="image.url_thumbnail || image.url_principale" alt="Photo produit" class="h-24 w-full object-cover"><button type="button" @click="confirmDeleteImage(image.id)" class="absolute right-2 top-2 inline-flex h-7 w-7 items-center justify-center rounded-full bg-red-500 text-xs font-black text-white shadow-lg hover:bg-red-400">X</button></div></template></div><div class="mt-2 text-xs text-white/55">Clique sur le X pour supprimer une photo enregistree.</div></div></div><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-3"><input type="checkbox" name="actif" value="1" x-model="form.actif"><span class="text-sm font-semibold">Produit actif</span></label><label class="flex items-center gap-3 rounded-2xl border border-white/10 bg-black/20 px-4 py-3"><input type="checkbox" name="enable_tier_pricing" value="1" x-model="form.enable_tier_pricing"><span class="text-sm font-semibold">Prix par palier</span></label></div><div class="rounded-3xl border border-white/10 p-4 space-y-3" x-show="form.enable_tier_pricing"><div class="font-bold">Liste des paliers</div><template x-for="(tier,index) in form.tiers" :key="index"><div class="grid grid-cols-3 gap-3"><input :name="'tier_min['+index+']'" x-model="tier.quantity_min" placeholder="Min" class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3"><input :name="'tier_max['+index+']'" x-model="tier.quantity_max" placeholder="Max" class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3"><input :name="'tier_price['+index+']'" x-model="tier.price" placeholder="Prix" class="rounded-2xl border border-white/10 bg-black/20 px-4 py-3"></div></template><button type="button" @click="form.tiers.push({quantity_min:'',quantity_max:'',price:''})" class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold">Ajouter ligne</button></div><button class="w-full rounded-2xl px-4 py-3 text-sm font-extrabold text-white" style="background:linear-gradient(135deg,#1E6FD9,#0f3b8c)">Enregistrer</button></form><form x-ref="deleteImageForm" method="POST" class="hidden" data-confirm-delete data-confirm-title="Supprimer cette photo ?" data-confirm-message="Cette photo sera retiree du produit." data-confirm-button="Oui, supprimer la photo">@csrf @method('DELETE')</form></div></div>
</div>
<script>
function productManager() {
    const baseAction = "{{ url('/admin/produits') }}";
    const emptyForm = {
        id: null,
        code_barre: '',
        designation: '',
        description: '',
        pv: '',
        stock: 0,
        id_category: '',
        actif: true,
        enable_tier_pricing: false,
        image_principale: '',
        images: [],
        tiers: [{ quantity_min: '', quantity_max: '', price: '' }],
    };

    return {
        open: false,
        editing: false,
        action: baseAction,
        previewUrl: '',
        previewObjectUrl: null,
        form: JSON.parse(JSON.stringify(emptyForm)),
        create() {
            this.open = true;
            this.editing = false;
            this.action = baseAction;
            this.form = JSON.parse(JSON.stringify(emptyForm));
            this.setPreview('');
            this.resetFileInput();
        },
        edit(product) {
            this.open = true;
            this.editing = true;
            this.action = `${baseAction}/${product.id}`;
            this.form = {
                id: product.id,
                code_barre: product.code_barre ?? '',
                designation: product.designation ?? '',
                description: product.description ?? '',
                pv: product.pv ?? '',
                stock: product.stock ?? 0,
                id_category: product.id_category ?? '',
                image_principale: product.image_principale ?? '',
                images: product.images ?? [],
                actif: Number(product.actif) === 1,
                enable_tier_pricing: !!product.enable_tier_pricing,
                tiers: product.tiers && product.tiers.length
                    ? product.tiers
                    : [{ quantity_min: '', quantity_max: '', price: '' }],
            };
            this.setPreview(this.form.image_principale);
            this.resetFileInput();
        },
        close() {
            this.open = false;
            this.setPreview('');
            this.resetFileInput();
        },
        resetFileInput() {
            this.$nextTick(() => {
                if (this.$refs.imagesInput) {
                    this.$refs.imagesInput.value = '';
                }
            });
        },
        setPreview(url) {
            if (this.previewObjectUrl) {
                URL.revokeObjectURL(this.previewObjectUrl);
                this.previewObjectUrl = null;
            }

            this.previewUrl = url || '';
        },
        previewImages(event) {
            const files = event.target.files;

            if (files && files[0]) {
                this.setPreview('');
                this.previewObjectUrl = URL.createObjectURL(files[0]);
                this.previewUrl = this.previewObjectUrl;
                return;
            }

            this.setPreview(this.editing ? (this.form.image_principale ?? '') : '');
        },
        confirmDeleteImage(imageId) {
            if (!this.form.id || !imageId || !this.$refs.deleteImageForm) {
                return;
            }

            this.$refs.deleteImageForm.action = `${baseAction}/${this.form.id}/images/${imageId}`;
            this.$refs.deleteImageForm.requestSubmit();
        },
    };
}
</script>
@endsection



@extends('layouts.admin')
@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
@foreach([
    ['label' => 'Distributeurs', 'value' => $stats['distributors'], 'icon' => 'fa-store'],
    ['label' => 'Clients', 'value' => $stats['clients'], 'icon' => 'fa-users'],
    ['label' => 'Produits', 'value' => $stats['products'], 'icon' => 'fa-boxes-stacked'],
    ['label' => 'Commandes', 'value' => $stats['orders'], 'icon' => 'fa-cart-shopping'],
    ['label' => 'CA', 'value' => number_format($stats['revenue'], 2, '.', ' ').' DA', 'icon' => 'fa-sack-dollar'],
] as $item)
<div class="rounded-3xl border border-white/10 bg-[var(--admin-card)] p-5"><div class="flex items-center justify-between"><div><div class="text-sm text-white/60">{{ $item['label'] }}</div><div class="mt-2 text-3xl font-extrabold">{{ $item['value'] }}</div></div><div class="h-12 w-12 rounded-2xl flex items-center justify-center text-white" style="background:linear-gradient(135deg,#1E6FD9,#0f3b8c)"><i class="fa-solid {{ $item['icon'] }}"></i></div></div></div>
@endforeach
</div>
<div class="rounded-3xl border border-white/10 bg-[var(--admin-card)] p-5">
    <div class="mb-4 flex items-center justify-between"><div class="font-extrabold tracking-wide">Dernieres commandes</div><a href="{{ url('/admin/commandes') }}" class="text-sm text-[var(--admin-primary)]">Voir tout</a></div>
    <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="text-white/60"><tr><th class="py-3 pr-4 text-left">#</th><th class="py-3 pr-4 text-left">Client</th><th class="py-3 pr-4 text-left">Distributeur</th><th class="py-3 pr-4 text-left">Statut</th><th class="py-3 text-right">Montant</th></tr></thead><tbody class="divide-y divide-white/10">@forelse($latestOrders as $order)<tr><td class="py-3 pr-4 font-semibold"><a href="{{ url('/admin/commandes/'.$order->id) }}">#{{ $order->id }}</a></td><td class="py-3 pr-4">{{ $order->client?->prenom }} {{ $order->client?->nom }}</td><td class="py-3 pr-4">{{ $order->fournisseur?->nom_frs }}</td><td class="py-3 pr-4"><span class="rounded-full bg-white/10 px-2.5 py-1 text-xs font-bold">{{ $order->statut }}</span></td><td class="py-3 text-right font-bold">{{ number_format($order->montant_total, 2, '.', ' ') }} DA</td></tr>@empty<tr><td colspan="5" class="py-8 text-center text-white/60">Aucune commande</td></tr>@endforelse</tbody></table></div>
</div>
@endsection

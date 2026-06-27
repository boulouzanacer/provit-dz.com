@extends('layouts.admin')
@section('content')
@php
    $cards = [
        ['label' => 'Distributeurs', 'value' => $stats['distributors'], 'meta' => $stats['active_distributors'].' actifs', 'icon' => 'fa-store', 'colors' => 'from-sky-500 via-blue-500 to-indigo-600'],
        ['label' => 'Clients', 'value' => $stats['clients'], 'meta' => 'Base clients globale', 'icon' => 'fa-users', 'colors' => 'from-violet-500 via-fuchsia-500 to-pink-500'],
        ['label' => 'Categories', 'value' => $stats['categories'], 'meta' => $stats['active_products'].' produits actifs', 'icon' => 'fa-tags', 'colors' => 'from-amber-500 via-orange-500 to-red-500'],
        ['label' => 'Commandes', 'value' => $stats['orders'], 'meta' => $stats['pending_orders'].' en cours', 'icon' => 'fa-cart-shopping', 'colors' => 'from-emerald-500 via-teal-500 to-cyan-500'],
    ];
    $statusUi = [
        'en_attente' => 'bg-amber-500/15 text-amber-200 border border-amber-400/20',
        'confirmee' => 'bg-sky-500/15 text-sky-200 border border-sky-400/20',
        'en_preparation' => 'bg-violet-500/15 text-violet-200 border border-violet-400/20',
        'expediee' => 'bg-cyan-500/15 text-cyan-200 border border-cyan-400/20',
        'livree' => 'bg-emerald-500/15 text-emerald-200 border border-emerald-400/20',
        'annulee' => 'bg-red-500/15 text-red-200 border border-red-400/20',
    ];
@endphp

<div class="space-y-6">
    <section class="rounded-[30px] border border-white/10 bg-[var(--admin-card)] p-5 shadow-2xl shadow-slate-950/10">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <div class="text-sm font-semibold text-white/60">Rubrique statistiques</div>
                <div class="mt-2 text-sm text-white/60">Analyse les commandes et le chiffre d affaires sur une periode precise.</div>
            </div>
            <form method="GET" action="{{ url('/admin/dashboard') }}" class="grid gap-3 md:grid-cols-3 xl:min-w-[720px]">
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.22em] text-white/45">Du</label>
                    <input type="datetime-local" name="from" value="{{ $range['from'] }}" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.22em] text-white/45">Au</label>
                    <input type="datetime-local" name="to" value="{{ $range['to'] }}" class="w-full rounded-2xl border border-white/10 bg-black/20 px-4 py-3 outline-none focus:border-[var(--admin-primary)]">
                </div>
                <div class="flex items-end gap-3">
                    <button class="inline-flex flex-1 items-center justify-center rounded-2xl px-4 py-3 text-sm font-extrabold text-white" style="background:linear-gradient(135deg,#1E6FD9,#0f3b8c)">Appliquer</button>
                    <a href="{{ url('/admin/dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold hover:bg-white/10">Reset</a>
                </div>
            </form>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            @foreach([
                ['label' => 'Commandes', 'value' => $filteredStats['orders'], 'meta' => 'Sur la periode', 'icon' => 'fa-cart-shopping', 'tone' => 'from-sky-500 to-blue-600'],
                ['label' => 'En cours', 'value' => $filteredStats['pending_orders'], 'meta' => 'Attente, confirmees, preparation', 'icon' => 'fa-hourglass-half', 'tone' => 'from-amber-500 to-orange-500'],
                ['label' => 'Livrees', 'value' => $filteredStats['delivered_orders'], 'meta' => 'Commandes finalisees', 'icon' => 'fa-circle-check', 'tone' => 'from-emerald-500 to-teal-500'],
                ['label' => 'Annulees', 'value' => $filteredStats['cancelled_orders'], 'meta' => 'Commandes annulees', 'icon' => 'fa-ban', 'tone' => 'from-rose-500 to-red-500'],
                ['label' => 'Panier moyen', 'value' => number_format($filteredStats['average_order'], 2, '.', ' ').' DA', 'meta' => 'Moyenne par commande', 'icon' => 'fa-chart-line', 'tone' => 'from-violet-500 to-fuchsia-500'],
            ] as $item)
                <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-white/65">{{ $item['label'] }}</div>
                            <div class="mt-3 text-3xl font-black text-white">{{ $item['value'] }}</div>
                            <div class="mt-2 text-xs text-white/55">{{ $item['meta'] }}</div>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br {{ $item['tone'] }} text-lg text-white">
                            <i class="fa-solid {{ $item['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden rounded-[32px] border border-white/10 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.32),_transparent_30%),linear-gradient(135deg,rgba(15,23,42,0.98),rgba(30,41,59,0.92))] p-6 lg:p-8">
        <div class="grid gap-6 xl:grid-cols-[1.65fr_0.95fr]">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-sky-400/20 bg-sky-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-sky-200">
                    Vue globale
                </div>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-white/70">
                    Suivez les distributeurs, le catalogue, les commandes et le chiffre d affaires depuis une vue synthetique avec priorites visuelles et acces rapides.
                </p>

                <div class="mt-6 rounded-[30px] border border-rose-400/20 bg-gradient-to-br from-rose-500/20 via-red-500/15 to-orange-500/20 p-5 shadow-2xl shadow-red-950/15">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-rose-100">
                                Chiffre d affaires
                            </div>
                            <div class="mt-4 text-4xl font-black text-white lg:text-5xl">{{ number_format($filteredStats['revenue'], 2, '.', ' ') }} DA</div>
                            <div class="mt-3 text-sm text-white/70">{{ $filteredStats['delivered_orders'] }} commandes livrees contribuent au chiffre d affaires sur la periode selectionnee.</div>
                        </div>
                        <div class="flex h-20 w-20 items-center justify-center rounded-[28px] bg-gradient-to-br from-rose-500 via-red-500 to-orange-500 text-3xl text-white shadow-xl shadow-red-950/20">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </div>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-2">
                    @foreach($cards as $card)
                        <div class="rounded-3xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm {{ $card['class'] ?? '' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-white/65">{{ $card['label'] }}</div>
                                    <div class="mt-3 text-3xl font-black text-white">{{ $card['value'] }}</div>
                                    <div class="mt-2 text-xs text-white/55">{{ $card['meta'] }}</div>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br {{ $card['colors'] }} text-xl text-white shadow-lg shadow-slate-950/20">
                                    <i class="fa-solid {{ $card['icon'] }}"></i>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[28px] border border-white/10 bg-slate-950/35 p-5 backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold text-white/60">Actions rapides</div>
                        <div class="mt-1 text-xl font-extrabold text-white">Gestion quotidienne</div>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-white">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    <a href="{{ url('/admin/distributeurs') }}" class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3 transition hover:bg-white/10">
                        <div>
                            <div class="font-bold text-white">Distributeurs</div>
                            <div class="text-xs text-white/55">Suivre l activite reseau</div>
                        </div>
                        <i class="fa-solid fa-arrow-right text-sky-300"></i>
                    </a>
                    <a href="{{ url('/admin/produits') }}" class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3 transition hover:bg-white/10">
                        <div>
                            <div class="font-bold text-white">Catalogue produits</div>
                            <div class="text-xs text-white/55">Mettre a jour les references</div>
                        </div>
                        <i class="fa-solid fa-arrow-right text-fuchsia-300"></i>
                    </a>
                    <a href="{{ url('/admin/commandes') }}" class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3 transition hover:bg-white/10">
                        <div>
                            <div class="font-bold text-white">Commandes</div>
                            <div class="text-xs text-white/55">Controler les statuts critiques</div>
                        </div>
                        <i class="fa-solid fa-arrow-right text-emerald-300"></i>
                    </a>
                </div>

                <div class="mt-6 rounded-3xl border border-white/10 bg-white/5 p-4">
                    <div class="text-sm font-semibold text-white/60">Flux commandes</div>
                    <div class="mt-4 space-y-3">
                        @php
                            $progressItems = [
                                ['label' => 'En attente', 'key' => 'en_attente', 'width' => $stats['orders'] > 0 ? max(8, min(100, (int) round(($statusBreakdown['en_attente'] / $stats['orders']) * 100))) : 8],
                                ['label' => 'Confirmees', 'key' => 'confirmee', 'width' => $stats['orders'] > 0 ? max(8, min(100, (int) round(($statusBreakdown['confirmee'] / $stats['orders']) * 100))) : 8],
                                ['label' => 'Livrees', 'key' => 'livree', 'width' => $stats['orders'] > 0 ? max(8, min(100, (int) round(($statusBreakdown['livree'] / $stats['orders']) * 100))) : 8],
                                ['label' => 'Annulees', 'key' => 'annulee', 'width' => $stats['orders'] > 0 ? max(8, min(100, (int) round(($statusBreakdown['annulee'] / $stats['orders']) * 100))) : 8],
                            ];
                        @endphp
                        @foreach($progressItems as $progress)
                            <div>
                                <div class="mb-1 flex items-center justify-between text-xs">
                                    <span class="text-white/65">{{ $progress['label'] }}</span>
                                    <span class="font-bold text-white">{{ $statusBreakdown[$progress['key']] }}</span>
                                </div>
                                @php
                                    $progressClass = 'h-2 w-full overflow-hidden rounded-full bg-white/10 accent-red-400';

                                    if ($progress['key'] === 'en_attente') {
                                        $progressClass = 'h-2 w-full overflow-hidden rounded-full bg-white/10 accent-amber-400';
                                    } elseif ($progress['key'] === 'confirmee') {
                                        $progressClass = 'h-2 w-full overflow-hidden rounded-full bg-white/10 accent-sky-400';
                                    } elseif ($progress['key'] === 'livree') {
                                        $progressClass = 'h-2 w-full overflow-hidden rounded-full bg-white/10 accent-emerald-400';
                                    }

                                    echo '<progress value="'.$progress['width'].'" max="100" class="'.$progressClass.'"></progress>';
                                @endphp
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.45fr_0.95fr]">
        <div class="rounded-[30px] border border-white/10 bg-[var(--admin-card)] p-5 shadow-2xl shadow-slate-950/10">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold text-white/60">Dernieres commandes</div>
                    <div class="mt-1 text-2xl font-extrabold">Suivi operationnel recent</div>
                </div>
                <a href="{{ url('/admin/commandes') }}" class="inline-flex items-center gap-2 rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold text-[var(--admin-primary)] transition hover:bg-white/10">
                    <span>Voir tout</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-white/60">
                        <tr>
                            <th class="py-3 pr-4 text-left">Commande</th>
                            <th class="py-3 pr-4 text-left">Client</th>
                            <th class="py-3 pr-4 text-left">Distributeur</th>
                            <th class="py-3 pr-4 text-left">Statut</th>
                            <th class="py-3 pr-4 text-left">Date</th>
                            <th class="py-3 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($latestOrders as $order)
                            <tr class="transition hover:bg-white/5">
                                <td class="py-3 pr-4">
                                    <a href="{{ url('/admin/commandes/'.$order->id) }}" class="font-extrabold text-white">#{{ $order->id }}</a>
                                </td>
                                <td class="py-3 pr-4">
                                    <div class="font-semibold">{{ $order->client?->prenom }} {{ $order->client?->nom }}</div>
                                </td>
                                <td class="py-3 pr-4">{{ $order->fournisseur?->nom_frs ?: '?' }}</td>
                                <td class="py-3 pr-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusUi[$order->statut] ?? 'bg-white/10 text-white' }}">
                                        {{ str_replace('_', ' ', $order->statut) }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 text-white/70">{{ optional($order->date_cmd)->format('d/m/Y H:i') }}</td>
                                <td class="py-3 text-right font-extrabold">{{ number_format($order->montant_total, 2, '.', ' ') }} DA</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-white/60">Aucune commande recente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-[30px] border border-white/10 bg-[var(--admin-card)] p-5 shadow-2xl shadow-slate-950/10">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold text-white/60">Repartition des statuts</div>
                        <div class="mt-1 text-2xl font-extrabold">Vue rapide</div>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-white">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        ['label' => 'En attente', 'key' => 'en_attente'],
                        ['label' => 'Confirmees', 'key' => 'confirmee'],
                        ['label' => 'Preparation', 'key' => 'en_preparation'],
                        ['label' => 'Expediees', 'key' => 'expediee'],
                        ['label' => 'Livrees', 'key' => 'livree'],
                        ['label' => 'Annulees', 'key' => 'annulee'],
                    ] as $item)
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-white/50">{{ $item['label'] }}</div>
                            <div class="mt-2 text-2xl font-black text-white">{{ $statusBreakdown[$item['key']] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-[30px] border border-white/10 bg-[var(--admin-card)] p-5 shadow-2xl shadow-slate-950/10">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold text-white/60">Distributeurs moteurs</div>
                        <div class="mt-1 text-2xl font-extrabold">Top activite</div>
                    </div>
                    <a href="{{ url('/admin/distributeurs') }}" class="text-sm font-semibold text-[var(--admin-primary)]">Ouvrir</a>
                </div>
                <div class="space-y-3">
                    @forelse($topDistributors as $distributor)
                        <div class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                            <div>
                                <div class="font-bold text-white">{{ $distributor->nom_frs }}</div>
                                <div class="text-xs text-white/55">{{ $distributor->ville ?: 'Ville non renseignee' }}</div>
                            </div>
                            <div class="text-right">
                        <div class="text-sm font-extrabold text-white">{{ $distributor->filtered_cmd1_count }} commandes</div>
                                <div class="text-xs {{ (int) $distributor->actif === 1 ? 'text-emerald-300' : 'text-red-300' }}">
                                    {{ $distributor->clients_count }} clients
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-8 text-center text-white/60">Aucun distributeur a afficher.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

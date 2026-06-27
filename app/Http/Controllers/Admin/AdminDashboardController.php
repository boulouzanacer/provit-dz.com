<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Models\Cmd1;
use App\Models\Fournisseur;
use App\Models\Produit;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        [$fromAt, $toAt, $range] = $this->resolvePeriod($request);

        $stats = [
            'distributors' => Fournisseur::query()->count(),
            'active_distributors' => Fournisseur::query()->where('actif', 1)->count(),
            'clients' => Client::query()->count(),
            'categories' => Category::query()->count(),
            'products' => Produit::query()->count(),
            'active_products' => Produit::query()->where('actif', 1)->count(),
            'orders' => Cmd1::query()->count(),
            'pending_orders' => Cmd1::query()->whereIn('statut', ['en_attente', 'confirmee', 'en_preparation'])->count(),
            'delivered_orders' => Cmd1::query()->where('statut', 'livree')->count(),
            'revenue' => (float) (Cmd1::query()->sum('montant_total') ?? 0),
        ];

        $filteredOrdersQuery = $this->applyPeriod(Cmd1::query(), $fromAt, $toAt);

        $filteredStats = [
            'orders' => (clone $filteredOrdersQuery)->count(),
            'pending_orders' => (clone $filteredOrdersQuery)->whereIn('statut', ['en_attente', 'confirmee', 'en_preparation'])->count(),
            'delivered_orders' => (clone $filteredOrdersQuery)->where('statut', 'livree')->count(),
            'cancelled_orders' => (clone $filteredOrdersQuery)->where('statut', 'annulee')->count(),
            'revenue' => (float) ((clone $filteredOrdersQuery)->where('statut', 'livree')->sum('montant_total') ?? 0),
        ];
        $filteredStats['average_order'] = $filteredStats['orders'] > 0
            ? round(((clone $filteredOrdersQuery)->sum('montant_total') ?? 0) / $filteredStats['orders'], 2)
            : 0.0;

        $latestOrders = Cmd1::query()
            ->with(['client:id,nom,prenom', 'fournisseur:id,nom_frs'])
            ->tap(fn (Builder $query) => $this->applyPeriod($query, $fromAt, $toAt))
            ->latest('date_cmd')
            ->limit(8)
            ->get();

        $statusBreakdown = [
            'en_attente' => (clone $filteredOrdersQuery)->where('statut', 'en_attente')->count(),
            'confirmee' => (clone $filteredOrdersQuery)->where('statut', 'confirmee')->count(),
            'en_preparation' => (clone $filteredOrdersQuery)->where('statut', 'en_preparation')->count(),
            'expediee' => (clone $filteredOrdersQuery)->where('statut', 'expediee')->count(),
            'livree' => (clone $filteredOrdersQuery)->where('statut', 'livree')->count(),
            'annulee' => (clone $filteredOrdersQuery)->where('statut', 'annulee')->count(),
        ];

        $topDistributors = Fournisseur::query()
            ->withCount('clients')
            ->withCount([
                'cmd1 as filtered_cmd1_count' => fn (Builder $query) => $this->applyPeriod($query, $fromAt, $toAt),
            ])
            ->orderByDesc('filtered_cmd1_count')
            ->limit(4)
            ->get(['id', 'nom_frs', 'ville', 'actif']);

        return view('admin.dashboard', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'filteredStats' => $filteredStats,
            'latestOrders' => $latestOrders,
            'statusBreakdown' => $statusBreakdown,
            'topDistributors' => $topDistributors,
            'range' => $range,
        ]);
    }

    private function resolvePeriod(Request $request): array
    {
        $fromRaw = trim((string) $request->query('from', ''));
        $toRaw = trim((string) $request->query('to', ''));

        $fromAt = $fromRaw !== '' ? Carbon::parse($fromRaw) : null;
        $toAt = $toRaw !== '' ? Carbon::parse($toRaw) : null;

        if ($fromAt && $toAt && $fromAt->greaterThan($toAt)) {
            [$fromAt, $toAt] = [$toAt, $fromAt];
            [$fromRaw, $toRaw] = [$toRaw, $fromRaw];
        }

        return [$fromAt, $toAt, [
            'from' => $fromRaw,
            'to' => $toRaw,
            'is_filtered' => $fromAt !== null || $toAt !== null,
        ]];
    }

    private function applyPeriod(Builder $query, ?Carbon $fromAt, ?Carbon $toAt): Builder
    {
        return $query
            ->when($fromAt, fn (Builder $builder) => $builder->where('date_cmd', '>=', $fromAt))
            ->when($toAt, fn (Builder $builder) => $builder->where('date_cmd', '<=', $toAt));
    }
}

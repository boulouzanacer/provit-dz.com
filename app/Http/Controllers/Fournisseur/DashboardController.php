<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Cmd1;
use App\Models\DistributorStock;
use App\Models\Produit;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $frsId = (int) session('frs_id');
        [$fromAt, $toAt, $range] = $this->resolvePeriod($request);

        $stats = [
            'clients' => Client::query()->where('id_frs', $frsId)->count(),
            'orders' => Cmd1::query()->where('id_frs', $frsId)->count(),
            'pending_orders' => Cmd1::query()->where('id_frs', $frsId)->where('statut', 'en_attente')->count(),
            'processing_orders' => Cmd1::query()->where('id_frs', $frsId)->whereIn('statut', ['confirmee', 'en_preparation', 'expediee'])->count(),
            'delivered_orders' => Cmd1::query()->where('id_frs', $frsId)->where('statut', 'livree')->count(),
            'stock_total' => (int) (DistributorStock::query()->where('id_frs', $frsId)->sum('quantite') ?? 0),
            'active_products' => Produit::query()
                ->where('actif', 1)
                ->whereHas('distributorStocks', fn ($query) => $query->where('id_frs', $frsId))
                ->count(),
            'low_stock_products' => DistributorStock::query()
                ->where('id_frs', $frsId)
                ->where('quantite', '>', 0)
                ->where('quantite', '<=', 5)
                ->count(),
            'empty_stock_products' => DistributorStock::query()
                ->where('id_frs', $frsId)
                ->where('quantite', '<=', 0)
                ->count(),
            'revenue' => (float) (Cmd1::query()->where('id_frs', $frsId)->where('statut', 'livree')->sum('montant_total') ?? 0),
        ];

        $filteredOrdersQuery = $this->applyPeriod(
            Cmd1::query()->where('id_frs', $frsId),
            $fromAt,
            $toAt
        );

        $filteredStats = [
            'orders' => (clone $filteredOrdersQuery)->count(),
            'pending_orders' => (clone $filteredOrdersQuery)->where('statut', 'en_attente')->count(),
            'processing_orders' => (clone $filteredOrdersQuery)->whereIn('statut', ['confirmee', 'en_preparation', 'expediee'])->count(),
            'delivered_orders' => (clone $filteredOrdersQuery)->where('statut', 'livree')->count(),
            'cancelled_orders' => (clone $filteredOrdersQuery)->where('statut', 'annulee')->count(),
            'revenue' => (float) ((clone $filteredOrdersQuery)->where('statut', 'livree')->sum('montant_total') ?? 0),
        ];
        $filteredStats['average_order'] = $filteredStats['orders'] > 0
            ? round(((clone $filteredOrdersQuery)->sum('montant_total') ?? 0) / $filteredStats['orders'], 2)
            : 0.0;
        $filteredStats['active_clients'] = Client::query()
            ->where('id_frs', $frsId)
            ->whereHas('commandes', fn (Builder $query) => $this->applyPeriod($query, $fromAt, $toAt))
            ->count();

        $latestOrders = Cmd1::query()
            ->with('client:id,nom,prenom')
            ->where('id_frs', $frsId)
            ->tap(fn (Builder $query) => $this->applyPeriod($query, $fromAt, $toAt))
            ->latest('date_cmd')
            ->limit(8)
            ->get();

        $topClients = Client::query()
            ->where('id_frs', $frsId)
            ->withCount([
                'commandes as filtered_commandes_count' => fn (Builder $query) => $this->applyPeriod($query, $fromAt, $toAt),
            ])
            ->orderByDesc('filtered_commandes_count')
            ->limit(4)
            ->get(['id', 'nom', 'prenom', 'email']);

        return view('fournisseur.dashboard', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'filteredStats' => $filteredStats,
            'latestOrders' => $latestOrders,
            'topClients' => $topClients,
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

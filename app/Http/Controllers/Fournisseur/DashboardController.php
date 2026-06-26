<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Cmd1;
use App\Models\DistributorStock;
use App\Models\Produit;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $frsId = (int) session('frs_id');

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

        $latestOrders = Cmd1::query()
            ->with('client:id,nom,prenom')
            ->where('id_frs', $frsId)
            ->latest('date_cmd')
            ->limit(8)
            ->get();

        $topClients = Client::query()
            ->where('id_frs', $frsId)
            ->withCount('commandes')
            ->orderByDesc('commandes_count')
            ->limit(4)
            ->get(['id', 'nom', 'prenom', 'email']);

        return view('fournisseur.dashboard', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'latestOrders' => $latestOrders,
            'topClients' => $topClients,
        ]);
    }
}

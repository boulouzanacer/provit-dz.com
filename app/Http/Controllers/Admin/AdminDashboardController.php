<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Client;
use App\Models\Cmd1;
use App\Models\Fournisseur;
use App\Models\Produit;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
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

        $latestOrders = Cmd1::query()
            ->with(['client:id,nom,prenom', 'fournisseur:id,nom_frs'])
            ->latest('date_cmd')
            ->limit(8)
            ->get();

        $statusBreakdown = [
            'en_attente' => Cmd1::query()->where('statut', 'en_attente')->count(),
            'confirmee' => Cmd1::query()->where('statut', 'confirmee')->count(),
            'en_preparation' => Cmd1::query()->where('statut', 'en_preparation')->count(),
            'expediee' => Cmd1::query()->where('statut', 'expediee')->count(),
            'livree' => Cmd1::query()->where('statut', 'livree')->count(),
            'annulee' => Cmd1::query()->where('statut', 'annulee')->count(),
        ];

        $topDistributors = Fournisseur::query()
            ->withCount(['clients', 'cmd1'])
            ->orderByDesc('cmd1_count')
            ->limit(4)
            ->get(['id', 'nom_frs', 'ville', 'actif']);

        return view('admin.dashboard', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'latestOrders' => $latestOrders,
            'statusBreakdown' => $statusBreakdown,
            'topDistributors' => $topDistributors,
        ]);
    }
}

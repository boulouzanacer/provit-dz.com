<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            'clients' => Client::query()->count(),
            'products' => Produit::query()->count(),
            'orders' => Cmd1::query()->count(),
            'revenue' => (float) (Cmd1::query()->sum('montant_total') ?? 0),
        ];

        $latestOrders = Cmd1::query()
            ->with(['client:id,nom,prenom', 'fournisseur:id,nom_frs'])
            ->latest('date_cmd')
            ->limit(6)
            ->get();

        return view('admin.dashboard', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'latestOrders' => $latestOrders,
        ]);
    }
}

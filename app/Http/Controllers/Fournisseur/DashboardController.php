<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Cmd1;
use App\Models\DistributorStock;
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
            'stock_total' => (int) (DistributorStock::query()->where('id_frs', $frsId)->sum('quantite') ?? 0),
        ];

        $latestOrders = Cmd1::query()
            ->with('client:id,nom,prenom')
            ->where('id_frs', $frsId)
            ->latest('date_cmd')
            ->limit(8)
            ->get();

        return view('fournisseur.dashboard', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'latestOrders' => $latestOrders,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $frsId = (int) session('frs_id');
        $q = trim((string) $request->query('q', ''));

        $clients = Client::query()
            ->where('id_frs', $frsId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nom', 'like', "%{$q}%")
                        ->orWhere('prenom', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('fournisseur.clients.index', [
            'title' => 'Mes Clients',
            'clients' => $clients,
            'q' => $q,
        ]);
    }

    public function show(int $id): View
    {
        $frsId = (int) session('frs_id');

        $client = Client::query()
            ->where('id_frs', $frsId)
            ->with(['commandes' => fn ($query) => $query->where('id_frs', $frsId)->latest('date_cmd')])
            ->findOrFail($id);

        return view('fournisseur.clients.show', [
            'title' => 'Client',
            'client' => $client,
        ]);
    }
}

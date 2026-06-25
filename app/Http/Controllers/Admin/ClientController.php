<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $clients = Client::query()
            ->with('fournisseur:id,nom_frs')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nom', 'like', "%{$q}%")
                        ->orWhere('prenom', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('telephone', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.clients.index', [
            'title' => 'Clients',
            'clients' => $clients,
            'q' => $q,
        ]);
    }

    public function show(int $id): View
    {
        $client = Client::query()
            ->with(['fournisseur:id,nom_frs', 'commandes.fournisseur:id,nom_frs'])
            ->findOrFail($id);

        return view('admin.clients.show', [
            'title' => 'Client',
            'client' => $client,
        ]);
    }

    public function destroy(int $id): RedirectResponse
    {
        $client = Client::query()->findOrFail($id);
        $client->delete();

        return redirect()->to('/admin/clients')->with('success', 'Client supprime.');
    }
}

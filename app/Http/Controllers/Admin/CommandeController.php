<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cmd1;
use App\Models\Fournisseur;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommandeController extends Controller
{
    public function index(Request $request): View
    {
        $status = trim((string) $request->query('status', ''));
        $idFrs = (int) $request->query('id_frs', 0);
        $q = trim((string) $request->query('q', ''));

        $orders = Cmd1::query()
            ->with(['client:id,nom,prenom,email', 'fournisseur:id,nom_frs'])
            ->when($status !== '', fn ($query) => $query->where('statut', $status))
            ->when($idFrs > 0, fn ($query) => $query->where('id_frs', $idFrs))
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('client', function ($sub) use ($q) {
                    $sub->where('nom', 'like', "%{$q}%")
                        ->orWhere('prenom', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->latest('date_cmd')
            ->paginate(20)
            ->withQueryString();

        return view('admin.commandes.index', [
            'title' => 'Commandes',
            'orders' => $orders,
            'distributors' => Fournisseur::query()->orderBy('nom_frs')->get(['id', 'nom_frs']),
            'status' => $status,
            'id_frs' => $idFrs,
            'q' => $q,
        ]);
    }

    public function show(int $id): View
    {
        $order = Cmd1::query()
            ->with(['client', 'fournisseur', 'lignes.produit'])
            ->findOrFail($id);

        return view('admin.commandes.show', [
            'title' => 'Commande #' . $order->id,
            'order' => $order,
        ]);
    }

    public function updateStatut(Request $request, int $id): RedirectResponse
    {
        $order = Cmd1::query()->findOrFail($id);

        $data = $request->validate([
            'statut' => ['required', Rule::in(['en_attente', 'confirmee', 'en_preparation', 'expediee', 'livree', 'annulee'])],
        ]);

        $order->update(['statut' => $data['statut']]);

        return back()->with('success', 'Statut de commande mis a jour.');
    }
}

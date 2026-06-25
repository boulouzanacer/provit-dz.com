<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Cmd1;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommandeController extends Controller
{
    public function index(Request $request): View
    {
        $frsId = (int) session('frs_id');
        $status = trim((string) $request->query('status', ''));

        $orders = Cmd1::query()
            ->with('client:id,nom,prenom,email')
            ->where('id_frs', $frsId)
            ->when($status !== '', fn ($query) => $query->where('statut', $status))
            ->latest('date_cmd')
            ->paginate(20)
            ->withQueryString();

        return view('fournisseur.commandes.index', [
            'title' => 'Mes Commandes',
            'orders' => $orders,
            'status' => $status,
        ]);
    }

    public function show(int $id): View
    {
        $order = Cmd1::query()
            ->where('id_frs', (int) session('frs_id'))
            ->with(['client', 'lignes.produit'])
            ->findOrFail($id);

        return view('fournisseur.commandes.show', [
            'title' => 'Commande #' . $order->id,
            'order' => $order,
        ]);
    }

    public function updateStatut(Request $request, int $id): RedirectResponse
    {
        $order = Cmd1::query()->where('id_frs', (int) session('frs_id'))->findOrFail($id);

        $data = $request->validate([
            'statut' => ['required', Rule::in(['en_attente', 'confirmee', 'en_preparation', 'expediee', 'livree', 'annulee'])],
        ]);

        $order->update(['statut' => $data['statut']]);

        return back()->with('success', 'Statut mis a jour.');
    }
}

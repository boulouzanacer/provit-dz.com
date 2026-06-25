<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\DistributorStock;
use App\Models\Produit;
use App\Models\StockMovement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(): View
    {
        $frsId = (int) session('frs_id');

        $movements = StockMovement::query()
            ->with('produit:id,designation,code_barre')
            ->where('id_frs', $frsId)
            ->latest()
            ->paginate(20);

        $products = Produit::query()->where('actif', 1)->orderBy('designation')->get(['id', 'designation', 'code_barre']);

        return view('fournisseur.stocks.index', [
            'title' => 'Stocks',
            'movements' => $movements,
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $frsId = (int) session('frs_id');

        $data = $request->validate([
            'id_produit' => ['required', 'integer', 'exists:produit,id'],
            'quantite' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($frsId, $data) {
            $stock = DistributorStock::query()->firstOrCreate(
                ['id_frs' => $frsId, 'id_produit' => (int) $data['id_produit']],
                ['quantite' => 0]
            );

            $stock->update(['quantite' => (int) $stock->quantite + (int) $data['quantite']]);

            StockMovement::create([
                'id_frs' => $frsId,
                'id_produit' => (int) $data['id_produit'],
                'quantity' => (int) $data['quantite'],
                'movement_type' => 'manual_addition',
                'note' => $data['note'] ?? null,
            ]);
        });

        return back()->with('success', 'Stock ajoute.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $frsId = (int) session('frs_id');
        $movement = StockMovement::query()->where('id_frs', $frsId)->findOrFail($id);

        if ($movement->movement_type !== 'manual_addition') {
            return back()->with('error', 'Ce mouvement ne peut pas etre supprime.');
        }

        try {
            DB::transaction(function () use ($movement, $frsId) {
                $stock = DistributorStock::query()
                    ->where('id_frs', $frsId)
                    ->where('id_produit', $movement->id_produit)
                    ->lockForUpdate()
                    ->first();

                if (! $stock || (int) $stock->quantite < (int) $movement->quantity) {
                    throw new \RuntimeException('Impossible de supprimer ce mouvement car le stock a deja ete consomme.');
                }

                $stock->update(['quantite' => (int) $stock->quantite - (int) $movement->quantity]);
                $movement->delete();
            });
        } catch (\RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Mouvement de stock supprime.');
    }
}

<?php

namespace App\Http\Controllers\Fournisseur;

use App\Http\Controllers\Controller;
use App\Models\Produit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    public function index(Request $request): View
    {
        $frsId = (int) session('frs_id');
        $q = trim((string) $request->query('q', ''));

        $products = Produit::query()
            ->with(['category:id,nom', 'distributorStocks' => fn ($query) => $query->where('id_frs', $frsId)])
            ->when($q !== '', fn ($query) => $query->where('designation', 'like', "%{$q}%"))
            ->where('actif', 1)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('fournisseur.produits.index', [
            'title' => 'Mes Produits',
            'products' => $products,
            'q' => $q,
            'frsId' => $frsId,
        ]);
    }
}

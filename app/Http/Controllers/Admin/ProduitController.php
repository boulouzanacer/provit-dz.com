<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DistributorStock;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\ProduitQuantityPrice;
use App\Services\ImageProduitService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProduitController extends Controller
{
    public function __construct(private readonly ImageProduitService $imageService)
    {
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $products = Produit::query()
            ->with(['category:id,nom', 'images:id,id_produit,url_thumbnail,ordre', 'quantityPrices'])
            ->withCount('distributorStocks')
            ->when($q !== '', fn ($query) => $query->where('designation', 'like', "%{$q}%"))
            ->latest()
            ->get();

        $categories = Category::query()->orderBy('nom')->get(['id', 'nom']);

        return view('admin.produits.index', [
            'title' => 'Produits',
            'products' => $products,
            'categories' => $categories,
            'q' => $q,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);
        $enabled = $request->boolean('enable_tier_pricing');

        $product = DB::transaction(function () use ($request, $data, $enabled) {
            $product = Produit::create([
                'code_barre' => $data['code_barre'] ?? null,
                'designation' => $data['designation'],
                'description' => $data['description'] ?? null,
                'pv_1' => (float) $data['pv'],
                'pv_2' => (float) $data['pv'],
                'pv_3' => (float) $data['pv'],
                'stock' => (int) ($data['stock'] ?? 0),
                'id_category' => $data['id_category'] ?? null,
                'actif' => $request->boolean('actif', true),
                'enable_tier_pricing' => $enabled,
            ]);

            $this->ensureActiveProductIsAvailableForAllDistributors($product);
            $this->syncTiers($request, $product, $enabled);

            return $product;
        });

        if ($request->hasFile('images')) {
            $this->imageService->storeUploadedImages($product, $request->file('images', []));
        }

        return back()->with('success', 'Produit ajoute.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $product = Produit::query()->findOrFail($id);
        $data = $this->validateProduct($request, $product->id);
        $enabled = $request->boolean('enable_tier_pricing');

        DB::transaction(function () use ($request, $product, $data, $enabled) {
            $product->update([
                'code_barre' => $data['code_barre'] ?? null,
                'designation' => $data['designation'],
                'description' => $data['description'] ?? null,
                'pv_1' => (float) $data['pv'],
                'pv_2' => (float) $data['pv'],
                'pv_3' => (float) $data['pv'],
                'stock' => (int) ($data['stock'] ?? 0),
                'id_category' => $data['id_category'] ?? null,
                'actif' => $request->boolean('actif'),
                'enable_tier_pricing' => $enabled,
            ]);

            $this->ensureActiveProductIsAvailableForAllDistributors($product);
            $this->syncTiers($request, $product, $enabled);
        });

        if ($request->hasFile('images')) {
            $this->imageService->storeUploadedImages($product->fresh(), $request->file('images', []));
        }

        return back()->with('success', 'Produit modifie.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = Produit::query()->findOrFail($id);
        $product->delete();

        return back()->with('success', 'Produit supprime.');
    }

    public function toggleActif(int $id): RedirectResponse
    {
        $product = Produit::query()->findOrFail($id);
        $product->update(['actif' => (int) ! $product->actif]);

        $this->ensureActiveProductIsAvailableForAllDistributors($product->fresh());

        return back()->with('success', 'Statut du produit mis a jour.');
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code_barre' => ['nullable', 'string', 'max:120', Rule::unique('produit', 'code_barre')->ignore($ignoreId)],
            'designation' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'pv' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'id_category' => ['nullable', 'integer', 'exists:categories,id'],
            'actif' => ['nullable', 'boolean'],
            'enable_tier_pricing' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array'],
            'images.*' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function syncTiers(Request $request, Produit $product, bool $enabled): void
    {
        ProduitQuantityPrice::query()->where('id_produit', $product->id)->delete();

        if (! $enabled) {
            return;
        }

        $mins = $request->input('tier_min', []);
        $maxs = $request->input('tier_max', []);
        $prices = $request->input('tier_price', []);

        foreach ($mins as $index => $min) {
            if ($min === null || $min === '') {
                continue;
            }

            $price = $prices[$index] ?? null;
            if ($price === null || $price === '') {
                continue;
            }

            ProduitQuantityPrice::create([
                'id_produit' => $product->id,
                'quantity_min' => max(1, (int) $min),
                'quantity_max' => ($maxs[$index] ?? '') !== '' ? (int) $maxs[$index] : null,
                'price' => (float) $price,
            ]);
        }
    }

    private function ensureActiveProductIsAvailableForAllDistributors(Produit $product): void
    {
        if ((int) $product->actif !== 1) {
            return;
        }

        $distributorIds = Fournisseur::query()->pluck('id');

        if ($distributorIds->isEmpty()) {
            return;
        }

        $existingDistributorIds = DistributorStock::query()
            ->where('id_produit', $product->id)
            ->pluck('id_frs')
            ->all();

        $missingDistributorIds = $distributorIds->diff($existingDistributorIds);

        if ($missingDistributorIds->isEmpty()) {
            return;
        }

        $now = now();

        DistributorStock::query()->insert(
            $missingDistributorIds->map(fn (int $distributorId): array => [
                'id_frs' => $distributorId,
                'id_produit' => $product->id,
                'quantite' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ])->values()->all()
        );
    }
}

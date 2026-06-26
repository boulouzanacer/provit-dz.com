<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistributorStock;
use App\Models\Fournisseur;
use App\Models\Produit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FournisseurController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $distributors = Fournisseur::query()
            ->withCount(['clients', 'cmd1'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nom_frs', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('ville', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->get();

        return view('admin.fournisseurs.index', [
            'title' => 'Distributeurs',
            'distributors' => $distributors,
            'q' => $q,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('distributeurs/logos', 'public');
        }

        $data['password'] = Hash::make($data['password']);
        $data['actif'] = $request->boolean('actif', true);
        $data['is_visible'] = 1;

        DB::transaction(function () use ($data): void {
            $distributor = Fournisseur::create($data);

            $productIds = Produit::query()
                ->where('actif', 1)
                ->pluck('id');

            if ($productIds->isEmpty()) {
                return;
            }

            $now = now();

            DistributorStock::query()->insert(
                $productIds->map(fn (int $productId): array => [
                    'id_frs' => $distributor->id,
                    'id_produit' => $productId,
                    'quantite' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );
        });

        return back()->with('success', 'Distributeur ajoute.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $distributor = Fournisseur::query()->findOrFail($id);
        $data = $this->validateData($request, $distributor->id, false);

        if ($request->filled('password')) {
            $data['password'] = Hash::make((string) $request->input('password'));
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('logo')) {
            if ($distributor->logo_path) {
                Storage::disk('public')->delete($distributor->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('distributeurs/logos', 'public');
        }

        $data['actif'] = $request->boolean('actif');
        $distributor->update($data);

        return back()->with('success', 'Distributeur modifie.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $distributor = Fournisseur::query()->findOrFail($id);
        $distributor->delete();

        return back()->with('success', 'Distributeur supprime.');
    }

    public function toggleActif(int $id): RedirectResponse
    {
        $distributor = Fournisseur::query()->findOrFail($id);
        $distributor->update(['actif' => (int) ! $distributor->actif]);

        return back()->with('success', 'Statut du distributeur mis a jour.');
    }

    private function validateData(Request $request, ?int $ignoreId = null, bool $passwordRequired = true): array
    {
        $passwordRules = $passwordRequired
            ? ['required', 'string', 'min:8']
            : ['nullable', 'string', 'min:8'];

        return $request->validate([
            'nom_frs' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('frs', 'email')->ignore($ignoreId)],
            'password' => $passwordRules,
            'telephone' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:1000'],
            'ville' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'actif' => ['nullable', 'boolean'],
        ]);
    }
}

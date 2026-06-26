<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\FournisseurController;
use App\Models\DistributorStock;
use App\Models\Fournisseur;
use App\Models\Produit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDistributorStockInitializationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_distributor_receives_all_active_products_with_zero_stock(): void
    {
        $activeProductA = $this->createProduct('Liquide vaisselle', 1);
        $activeProductB = $this->createProduct('Javel', 1);
        $inactiveProduct = $this->createProduct('Ancien produit', 0);

        $session = app('session.store');
        $session->start();

        $request = Request::create('/admin/distributeurs', 'POST', [
            'nom_frs' => 'Distrib Oran',
            'email' => 'oran@example.com',
            'password' => 'password123',
            'telephone' => '0550000000',
            'adresse' => 'Oran',
            'ville' => 'Oran',
            'actif' => 1,
        ]);
        $request->setLaravelSession($session);

        app(FournisseurController::class)->store($request);

        $distributor = Fournisseur::query()->where('email', 'oran@example.com')->firstOrFail();

        $this->assertDatabaseHas('distributor_stocks', [
            'id_frs' => $distributor->id,
            'id_produit' => $activeProductA->id,
            'quantite' => 0,
        ]);

        $this->assertDatabaseHas('distributor_stocks', [
            'id_frs' => $distributor->id,
            'id_produit' => $activeProductB->id,
            'quantite' => 0,
        ]);

        $this->assertDatabaseMissing('distributor_stocks', [
            'id_frs' => $distributor->id,
            'id_produit' => $inactiveProduct->id,
        ]);

        $this->assertSame(2, DistributorStock::query()->where('id_frs', $distributor->id)->count());
        $this->assertTrue(Hash::check('password123', $distributor->password));
    }

    private function createProduct(string $designation, int $actif): Produit
    {
        return Produit::create([
            'code_barre' => Str::uuid()->toString(),
            'designation' => $designation,
            'description' => null,
            'pv_1' => 100,
            'pv_2' => 100,
            'pv_3' => 100,
            'stock' => 0,
            'image_principale' => null,
            'id_category' => null,
            'abonne_only' => 0,
            'enable_tier_pricing' => false,
            'actif' => $actif,
        ]);
    }
}

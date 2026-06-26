<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ProduitController;
use App\Models\DistributorStock;
use App\Models\Fournisseur;
use App\Services\ImageProduitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

class AdminProductDistributorPropagationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_active_product_is_added_to_all_distributors_with_zero_stock(): void
    {
        $distributorA = $this->createDistributor('alger@example.com');
        $distributorB = $this->createDistributor('oran@example.com');

        $this->mock(ImageProduitService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('storeUploadedImages')->never();
        });

        $session = app('session.store');
        $session->start();

        $request = Request::create('/admin/produits', 'POST', [
            'code_barre' => (string) Str::uuid(),
            'designation' => 'Nouveau Detergent',
            'description' => 'Produit test',
            'pv' => 250,
            'stock' => 0,
            'actif' => 1,
            'enable_tier_pricing' => 0,
        ]);
        $request->setLaravelSession($session);

        app(ProduitController::class)->store($request);

        $stocks = DistributorStock::query()->get();

        $this->assertCount(2, $stocks);
        $this->assertDatabaseHas('distributor_stocks', [
            'id_frs' => $distributorA->id,
            'quantite' => 0,
        ]);
        $this->assertDatabaseHas('distributor_stocks', [
            'id_frs' => $distributorB->id,
            'quantite' => 0,
        ]);
    }

    private function createDistributor(string $email): Fournisseur
    {
        return Fournisseur::create([
            'nom_frs' => 'Distrib ' . Str::before($email, '@'),
            'email' => $email,
            'password' => Hash::make('password'),
            'telephone' => '0550000000',
            'adresse' => 'Alger',
            'ville' => 'Alger',
            'token' => (string) Str::uuid(),
            'actif' => 1,
            'is_visible' => 1,
        ]);
    }
}

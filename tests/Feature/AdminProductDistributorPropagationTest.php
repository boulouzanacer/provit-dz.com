<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ProduitController;
use App\Http\Controllers\Fournisseur\ProduitController as DistributorProduitController;
use App\Models\DistributorStock;
use App\Models\Fournisseur;
use App\Models\Produit;
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

    public function test_existing_product_that_becomes_active_is_added_to_all_distributors_with_zero_stock(): void
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
            'designation' => 'Produit Reactif',
            'description' => 'Produit inactif au depart',
            'pv' => 250,
            'stock' => 0,
            'actif' => 0,
            'enable_tier_pricing' => 0,
        ]);
        $request->setLaravelSession($session);

        app(ProduitController::class)->store($request);

        $stockCountBeforeActivation = DistributorStock::query()->count();

        $productId = (int) \App\Models\Produit::query()
            ->where('designation', 'Produit Reactif')
            ->value('id');

        app(ProduitController::class)->toggleActif($productId);

        $this->assertSame(0, $stockCountBeforeActivation);
        $this->assertDatabaseHas('distributor_stocks', [
            'id_frs' => $distributorA->id,
            'id_produit' => $productId,
            'quantite' => 0,
        ]);
        $this->assertDatabaseHas('distributor_stocks', [
            'id_frs' => $distributorB->id,
            'id_produit' => $productId,
            'quantite' => 0,
        ]);
        $this->assertSame(2, DistributorStock::query()->where('id_produit', $productId)->count());
    }

    public function test_product_is_hidden_when_inactive_and_reappears_with_preserved_stock_when_reactivated(): void
    {
        $distributorA = $this->createDistributor('alger@example.com');
        $distributorB = $this->createDistributor('oran@example.com');

        $product = Produit::create([
            'code_barre' => (string) Str::uuid(),
            'designation' => 'Produit Cache',
            'description' => 'Produit avec stocks existants',
            'pv_1' => 250,
            'pv_2' => 250,
            'pv_3' => 250,
            'stock' => 0,
            'id_category' => null,
            'abonne_only' => 0,
            'enable_tier_pricing' => false,
            'actif' => 1,
        ]);

        DistributorStock::create([
            'id_frs' => $distributorA->id,
            'id_produit' => $product->id,
            'quantite' => 12,
        ]);

        DistributorStock::create([
            'id_frs' => $distributorB->id,
            'id_produit' => $product->id,
            'quantite' => 7,
        ]);

        app(ProduitController::class)->toggleActif($product->id);

        $inactiveView = $this->renderDistributorProducts($distributorA->id);
        $inactiveProducts = $inactiveView->getData()['products'];

        $this->assertSame(0, $inactiveProducts->count());
        $this->assertDatabaseHas('distributor_stocks', [
            'id_frs' => $distributorA->id,
            'id_produit' => $product->id,
            'quantite' => 12,
        ]);
        $this->assertDatabaseHas('distributor_stocks', [
            'id_frs' => $distributorB->id,
            'id_produit' => $product->id,
            'quantite' => 7,
        ]);

        app(ProduitController::class)->toggleActif($product->id);

        $activeView = $this->renderDistributorProducts($distributorA->id);
        $activeProducts = $activeView->getData()['products'];
        $visibleProduct = $activeProducts->first();

        $this->assertSame(1, $activeProducts->count());
        $this->assertNotNull($visibleProduct);
        $this->assertSame($product->id, $visibleProduct->id);
        $this->assertSame(12, $visibleProduct->stockForDistributor($distributorA->id));
        $this->assertDatabaseHas('distributor_stocks', [
            'id_frs' => $distributorA->id,
            'id_produit' => $product->id,
            'quantite' => 12,
        ]);
        $this->assertDatabaseHas('distributor_stocks', [
            'id_frs' => $distributorB->id,
            'id_produit' => $product->id,
            'quantite' => 7,
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

    private function renderDistributorProducts(int $frsId): \Illuminate\Contracts\View\View
    {
        $session = app('session.store');
        $session->start();
        $session->put('frs_id', $frsId);

        $request = Request::create('/distributeur/produits', 'GET');
        $request->setLaravelSession($session);

        return app(DistributorProduitController::class)->index($request);
    }
}

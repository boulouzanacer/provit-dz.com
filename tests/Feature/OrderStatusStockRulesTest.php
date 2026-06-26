<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Cmd1;
use App\Models\Cmd2;
use App\Models\DistributorStock;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\StockMovement;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class OrderStatusStockRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivered_order_decreases_distributor_stock(): void
    {
        [$order, $stock] = $this->createOrderWithStock(10);

        app(OrderStatusService::class)->updateStatus($order, 'livree');

        $this->assertDatabaseHas('cmd1', [
            'id' => $order->id,
            'statut' => 'livree',
        ]);
        $this->assertSame(7, $stock->fresh()->quantite);
        $this->assertDatabaseHas('stock_movements', [
            'id_cmd' => $order->id,
            'id_frs' => $order->id_frs,
            'id_produit' => $order->lignes()->firstOrFail()->id_produit,
            'quantity' => -3,
            'movement_type' => 'order_delivery',
        ]);
    }

    public function test_cancelled_order_keeps_stock_unchanged_when_not_delivered(): void
    {
        [$order, $stock] = $this->createOrderWithStock(10);

        app(OrderStatusService::class)->updateStatus($order, 'annulee');

        $this->assertDatabaseHas('cmd1', [
            'id' => $order->id,
            'statut' => 'annulee',
        ]);
        $this->assertSame(10, $stock->fresh()->quantite);
        $this->assertSame(0, StockMovement::query()->where('id_cmd', $order->id)->count());
    }

    public function test_finalized_order_status_cannot_be_changed_again(): void
    {
        [$order] = $this->createOrderWithStock(10);
        $service = app(OrderStatusService::class);

        $service->updateStatus($order, 'livree');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cette commande est deja finalisee. Son statut ne peut plus etre modifie.');

        $service->updateStatus($order->fresh(), 'annulee');
    }

    private function createOrderWithStock(int $initialStock): array
    {
        $distributor = Fournisseur::create([
            'nom_frs' => 'Distrib Test',
            'email' => 'distrib-' . Str::lower(Str::random(8)) . '@example.com',
            'password' => Hash::make('password'),
            'telephone' => '0550000000',
            'adresse' => 'Alger',
            'ville' => 'Alger',
            'token' => (string) Str::uuid(),
            'actif' => 1,
        ]);

        $client = Client::create([
            'code_client' => 'CLT-' . Str::upper(Str::random(6)),
            'nom' => 'Doe',
            'prenom' => 'John',
            'email' => 'client-' . Str::lower(Str::random(8)) . '@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'telephone' => '0550123456',
            'adresse' => 'Alger',
            'type_client' => 'simple',
            'tarif' => 1,
            'achat_client' => 0,
            'versement_client' => 0,
            'solde_client' => 0,
            'id_frs' => $distributor->id,
            'actif' => 1,
        ]);

        $product = Produit::create([
            'code_barre' => Str::uuid()->toString(),
            'designation' => 'Produit Commande',
            'description' => null,
            'pv_1' => 100,
            'pv_2' => 100,
            'pv_3' => 100,
            'stock' => 0,
            'image_principale' => null,
            'id_category' => null,
            'abonne_only' => 0,
            'enable_tier_pricing' => false,
            'actif' => 1,
        ]);

        $stock = DistributorStock::create([
            'id_frs' => $distributor->id,
            'id_produit' => $product->id,
            'quantite' => $initialStock,
        ]);

        $order = Cmd1::create([
            'id_client' => $client->id,
            'id_frs' => $distributor->id,
            'date_cmd' => now(),
            'statut' => 'en_attente',
            'montant_total' => 300,
            'adresse_livraison' => 'Alger',
            'notes' => null,
        ]);

        Cmd2::create([
            'id_cmd' => $order->id,
            'id_produit' => $product->id,
            'quantite' => 3,
            'prix_unitaire' => 100,
            'sous_total' => 300,
        ]);

        return [$order->fresh('lignes'), $stock];
    }
}

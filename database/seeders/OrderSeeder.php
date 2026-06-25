<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Cmd1;
use App\Models\Cmd2;
use App\Models\DistributorStock;
use App\Models\Produit;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::query()->where('email', 'sara@example.com')->first();
        $product = Produit::query()->where('code_barre', 'PV1001')->first();

        if (! $client || ! $product || ! $client->id_frs) {
            return;
        }

        $order = Cmd1::query()->firstOrCreate(
            ['id_client' => $client->id, 'id_frs' => $client->id_frs, 'notes' => 'Commande demo'],
            [
                'date_cmd' => now()->subDay(),
                'statut' => 'confirmee',
                'montant_total' => $product->pv_1 * 3,
                'adresse_livraison' => $client->adresse ?: 'Alger Centre',
            ]
        );

        Cmd2::query()->updateOrCreate(
            ['id_cmd' => $order->id, 'id_produit' => $product->id],
            ['quantite' => 3, 'prix_unitaire' => $product->pv_1, 'sous_total' => $product->pv_1 * 3]
        );

        StockMovement::query()->firstOrCreate(
            ['id_cmd' => $order->id, 'id_produit' => $product->id],
            ['id_frs' => $client->id_frs, 'quantity' => -3, 'movement_type' => 'order', 'note' => 'Commande demo']
        );

        $stock = DistributorStock::query()->where('id_frs', $client->id_frs)->where('id_produit', $product->id)->first();
        if ($stock && $stock->quantite < 37) {
            $stock->update(['quantite' => 37]);
        }
    }
}

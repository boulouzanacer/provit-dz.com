<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DistributorStock;
use App\Models\Fournisseur;
use App\Models\Produit;
use App\Models\ProduitQuantityPrice;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            ['code_barre' => 'PV1001', 'designation' => 'Lessive Liquide 2L', 'category' => 'Lessive', 'pv' => 480],
            ['code_barre' => 'PV1002', 'designation' => 'Eau de Javel 1L', 'category' => 'Javel', 'pv' => 150],
            ['code_barre' => 'PV1003', 'designation' => 'Liquide Vaisselle Citron', 'category' => 'Vaisselle', 'pv' => 220],
            ['code_barre' => 'PV1004', 'designation' => 'Nettoyant Sol Floral', 'category' => 'Nettoyants Multi-Usages', 'pv' => 350],
            ['code_barre' => 'PV1005', 'designation' => 'Degraissant Cuisine Pro', 'category' => 'Hygiene Professionnelle', 'pv' => 690],
        ];

        $distributors = Fournisseur::query()->get();

        foreach ($catalog as $row) {
            $category = Category::query()->where('nom', $row['category'])->first();

            $product = Produit::query()->updateOrCreate(
                ['code_barre' => $row['code_barre']],
                [
                    'designation' => $row['designation'],
                    'description' => 'Produit de demonstration pour Pro-Vit.',
                    'pv_1' => $row['pv'],
                    'pv_2' => $row['pv'],
                    'pv_3' => $row['pv'],
                    'stock' => 0,
                    'id_category' => $category?->id,
                    'actif' => 1,
                    'enable_tier_pricing' => 1,
                ]
            );

            ProduitQuantityPrice::query()->where('id_produit', $product->id)->delete();
            ProduitQuantityPrice::query()->create(['id_produit' => $product->id, 'quantity_min' => 1, 'quantity_max' => 9, 'price' => $row['pv']]);
            ProduitQuantityPrice::query()->create(['id_produit' => $product->id, 'quantity_min' => 10, 'quantity_max' => 29, 'price' => round($row['pv'] * 0.95, 2)]);
            ProduitQuantityPrice::query()->create(['id_produit' => $product->id, 'quantity_min' => 30, 'quantity_max' => null, 'price' => round($row['pv'] * 0.90, 2)]);

            foreach ($distributors as $index => $distributor) {
                DistributorStock::query()->updateOrCreate(
                    ['id_frs' => $distributor->id, 'id_produit' => $product->id],
                    ['quantite' => 40 + ($index * 10)]
                );
            }
        }
    }
}

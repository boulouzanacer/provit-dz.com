<?php

namespace Database\Seeders;

use App\Models\Fournisseur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FournisseurSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nom_frs' => 'Pro-Vit Alger Centre', 'email' => 'alger@provit-dz.com', 'ville' => 'Alger', 'telephone' => '0550 00 00 01', 'adresse' => 'Hydra, Alger'],
            ['nom_frs' => 'Pro-Vit Oran', 'email' => 'oran@provit-dz.com', 'ville' => 'Oran', 'telephone' => '0550 00 00 02', 'adresse' => 'Bir El Djir, Oran'],
            ['nom_frs' => 'Pro-Vit Setif', 'email' => 'setif@provit-dz.com', 'ville' => 'Setif', 'telephone' => '0550 00 00 03', 'adresse' => 'Centre ville, Setif'],
        ];

        foreach ($items as $item) {
            Fournisseur::query()->updateOrCreate(
                ['email' => $item['email']],
                $item + [
                    'password' => Hash::make('password'),
                    'actif' => 1,
                    'is_visible' => 1,
                ]
            );
        }
    }
}

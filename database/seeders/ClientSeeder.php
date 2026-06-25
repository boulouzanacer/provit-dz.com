<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Fournisseur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $alger = Fournisseur::query()->where('email', 'alger@provit-dz.com')->first();
        $oran = Fournisseur::query()->where('email', 'oran@provit-dz.com')->first();

        $clients = [
            ['nom' => 'Benali', 'prenom' => 'Sara', 'email' => 'sara@example.com', 'telephone' => '0660 11 22 33', 'adresse' => 'Alger Centre', 'id_frs' => $alger?->id],
            ['nom' => 'Touati', 'prenom' => 'Nadir', 'email' => 'nadir@example.com', 'telephone' => '0660 11 22 44', 'adresse' => 'Oran', 'id_frs' => $oran?->id],
        ];

        foreach ($clients as $index => $client) {
            Client::query()->updateOrCreate(
                ['email' => $client['email']],
                $client + [
                    'code_client' => 'CLT-' . str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                    'password' => Hash::make('password'),
                    'type_client' => 'simple',
                    'tarif' => 1,
                    'actif' => 1,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}

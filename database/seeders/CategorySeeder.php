<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Lessive',
            'Javel',
            'Nettoyants Multi-Usages',
            'Vaisselle',
            'Hygiene Professionnelle',
        ] as $name) {
            Category::query()->updateOrCreate(['nom' => $name], ['actif' => 1]);
        }
    }
}

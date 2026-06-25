<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayaSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['ID_WILAYA' => 1, 'WILAYA' => 'Adrar', 'WILAYA2' => 'Adrar'],
            ['ID_WILAYA' => 2, 'WILAYA' => 'Chlef', 'WILAYA2' => 'Chlef'],
            ['ID_WILAYA' => 3, 'WILAYA' => 'Laghouat', 'WILAYA2' => 'Laghouat'],
            ['ID_WILAYA' => 4, 'WILAYA' => 'Oum El Bouaghi', 'WILAYA2' => 'Oum El Bouaghi'],
            ['ID_WILAYA' => 5, 'WILAYA' => 'Batna', 'WILAYA2' => 'Batna'],
            ['ID_WILAYA' => 6, 'WILAYA' => 'Béjaïa', 'WILAYA2' => 'Béjaïa'],
            ['ID_WILAYA' => 7, 'WILAYA' => 'Biskra', 'WILAYA2' => 'Biskra'],
            ['ID_WILAYA' => 8, 'WILAYA' => 'Béchar', 'WILAYA2' => 'Béchar'],
            ['ID_WILAYA' => 9, 'WILAYA' => 'Blida', 'WILAYA2' => 'Blida'],
            ['ID_WILAYA' => 10, 'WILAYA' => 'Bouira', 'WILAYA2' => 'Bouira'],
            ['ID_WILAYA' => 11, 'WILAYA' => 'Tamanrasset', 'WILAYA2' => 'Tamanrasset'],
            ['ID_WILAYA' => 12, 'WILAYA' => 'Tébessa', 'WILAYA2' => 'Tébessa'],
            ['ID_WILAYA' => 13, 'WILAYA' => 'Tlemcen', 'WILAYA2' => 'Tlemcen'],
            ['ID_WILAYA' => 14, 'WILAYA' => 'Tiaret', 'WILAYA2' => 'Tiaret'],
            ['ID_WILAYA' => 15, 'WILAYA' => 'Tizi Ouzou', 'WILAYA2' => 'Tizi Ouzou'],
            ['ID_WILAYA' => 16, 'WILAYA' => 'Alger', 'WILAYA2' => 'Alger'],
            ['ID_WILAYA' => 17, 'WILAYA' => 'Djelfa', 'WILAYA2' => 'Djelfa'],
            ['ID_WILAYA' => 18, 'WILAYA' => 'Jijel', 'WILAYA2' => 'Jijel'],
            ['ID_WILAYA' => 19, 'WILAYA' => 'Sétif', 'WILAYA2' => 'Sétif'],
            ['ID_WILAYA' => 20, 'WILAYA' => 'Saïda', 'WILAYA2' => 'Saïda'],
            ['ID_WILAYA' => 21, 'WILAYA' => 'Skikda', 'WILAYA2' => 'Skikda'],
            ['ID_WILAYA' => 22, 'WILAYA' => 'Sidi Bel Abbès', 'WILAYA2' => 'Sidi Bel Abbès'],
            ['ID_WILAYA' => 23, 'WILAYA' => 'Annaba', 'WILAYA2' => 'Annaba'],
            ['ID_WILAYA' => 24, 'WILAYA' => 'Guelma', 'WILAYA2' => 'Guelma'],
            ['ID_WILAYA' => 25, 'WILAYA' => 'Constantine', 'WILAYA2' => 'Constantine'],
            ['ID_WILAYA' => 26, 'WILAYA' => 'Médéa', 'WILAYA2' => 'Médéa'],
            ['ID_WILAYA' => 27, 'WILAYA' => 'Mostaganem', 'WILAYA2' => 'Mostaganem'],
            ['ID_WILAYA' => 28, 'WILAYA' => "M'Sila", 'WILAYA2' => "M'Sila"],
            ['ID_WILAYA' => 29, 'WILAYA' => 'Mascara', 'WILAYA2' => 'Mascara'],
            ['ID_WILAYA' => 30, 'WILAYA' => 'Ouargla', 'WILAYA2' => 'Ouargla'],
            ['ID_WILAYA' => 31, 'WILAYA' => 'Oran', 'WILAYA2' => 'Oran'],
            ['ID_WILAYA' => 32, 'WILAYA' => 'El Bayadh', 'WILAYA2' => 'El Bayadh'],
            ['ID_WILAYA' => 33, 'WILAYA' => 'Illizi', 'WILAYA2' => 'Illizi'],
            ['ID_WILAYA' => 34, 'WILAYA' => 'Bordj Bou Arréridj', 'WILAYA2' => 'Bordj Bou Arréridj'],
            ['ID_WILAYA' => 35, 'WILAYA' => 'Boumerdès', 'WILAYA2' => 'Boumerdès'],
            ['ID_WILAYA' => 36, 'WILAYA' => 'El Tarf', 'WILAYA2' => 'El Tarf'],
            ['ID_WILAYA' => 37, 'WILAYA' => 'Tindouf', 'WILAYA2' => 'Tindouf'],
            ['ID_WILAYA' => 38, 'WILAYA' => 'Tissemsilt', 'WILAYA2' => 'Tissemsilt'],
            ['ID_WILAYA' => 39, 'WILAYA' => 'El Oued', 'WILAYA2' => 'El Oued'],
            ['ID_WILAYA' => 40, 'WILAYA' => 'Khenchela', 'WILAYA2' => 'Khenchela'],
            ['ID_WILAYA' => 41, 'WILAYA' => 'Souk Ahras', 'WILAYA2' => 'Souk Ahras'],
            ['ID_WILAYA' => 42, 'WILAYA' => 'Tipaza', 'WILAYA2' => 'Tipaza'],
            ['ID_WILAYA' => 43, 'WILAYA' => 'Mila', 'WILAYA2' => 'Mila'],
            ['ID_WILAYA' => 44, 'WILAYA' => 'Aïn Defla', 'WILAYA2' => 'Aïn Defla'],
            ['ID_WILAYA' => 45, 'WILAYA' => 'Naâma', 'WILAYA2' => 'Naâma'],
            ['ID_WILAYA' => 46, 'WILAYA' => 'Aïn Témouchent', 'WILAYA2' => 'Aïn Témouchent'],
            ['ID_WILAYA' => 47, 'WILAYA' => 'Ghardaïa', 'WILAYA2' => 'Ghardaïa'],
            ['ID_WILAYA' => 48, 'WILAYA' => 'Relizane', 'WILAYA2' => 'Relizane'],
            ['ID_WILAYA' => 49, 'WILAYA' => 'Timimoun', 'WILAYA2' => 'Timimoun'],
            ['ID_WILAYA' => 50, 'WILAYA' => 'Bordj Badji Mokhtar', 'WILAYA2' => 'Bordj Badji Mokhtar'],
            ['ID_WILAYA' => 51, 'WILAYA' => 'Ouled Djellal', 'WILAYA2' => 'Ouled Djellal'],
            ['ID_WILAYA' => 52, 'WILAYA' => 'Béni Abbès', 'WILAYA2' => 'Béni Abbès'],
            ['ID_WILAYA' => 53, 'WILAYA' => 'In Salah', 'WILAYA2' => 'In Salah'],
            ['ID_WILAYA' => 54, 'WILAYA' => 'In Guezzam', 'WILAYA2' => 'In Guezzam'],
            ['ID_WILAYA' => 55, 'WILAYA' => 'Touggourt', 'WILAYA2' => 'Touggourt'],
            ['ID_WILAYA' => 56, 'WILAYA' => 'Djanet', 'WILAYA2' => 'Djanet'],
            ['ID_WILAYA' => 57, 'WILAYA' => "El M'Ghair", 'WILAYA2' => "El M'Ghair"],
            ['ID_WILAYA' => 58, 'WILAYA' => 'El Meniaa', 'WILAYA2' => 'El Meniaa'],
        ];

        DB::table('wilaya')->upsert(
            $rows,
            ['ID_WILAYA'],
            ['WILAYA', 'WILAYA2']
        );
    }
}

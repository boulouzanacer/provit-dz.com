<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommuneSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/wilayas_communes.json');
        $raw = trim((string) file_get_contents($path));

        if (str_starts_with($raw, 'const ')) {
            $start = strpos($raw, '{');
            $end = strrpos($raw, '}');

            if ($start !== false && $end !== false && $end > $start) {
                $raw = substr($raw, $start, $end - $start + 1);
            }
        }

        $data = json_decode($raw, true);

        if (! is_array($data)) {
            throw new \RuntimeException('Impossible de parser le fichier wilayas_communes.json');
        }

        $rows = [];

        foreach ($data as $wilayaName => $communes) {
            foreach ($communes as $commune) {
                $rows[] = [
                    'ID_COMMUNE' => (int) $commune['id'],
                    'COMMUNE' => $commune['commune_name'],
                    'ID_WILAYA' => (int) $commune['wilaya_code'],
                ];
            }
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            DB::table('commune')->upsert(
                $chunk,
                ['ID_COMMUNE'],
                ['COMMUNE', 'ID_WILAYA']
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'company_name' => 'Pro-Vit',
            'company_phone' => '0550 00 00 00',
            'company_email' => 'contact@provit-dz.com',
            'company_address' => 'Alger, Algerie',
        ];

        foreach ($settings as $key => $value) {
            Setting::putValue($key, $value);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use Illuminate\Database\Seeder;

class KecamatanSeeder extends Seeder
{
    public function run(): void
    {
        $kecamatans = [
            ['code' => '001', 'name' => 'Buleleng'],
            ['code' => '002', 'name' => 'Sukasada'],
            ['code' => '003', 'name' => 'Sawan'],
            ['code' => '004', 'name' => 'Kubutambahan'],
            ['code' => '005', 'name' => 'Tejakula'],
            ['code' => '006', 'name' => 'Gerokgak'],
            ['code' => '007', 'name' => 'Seririt'],
            ['code' => '008', 'name' => 'Banjar'],
            ['code' => '009', 'name' => 'Busungbiu'],
        ];

        foreach ($kecamatans as $data) {
            Kecamatan::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
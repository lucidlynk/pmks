<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\Village;
use Illuminate\Database\Seeder;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Buleleng' => [
                ['name' => 'Astina',           'code' => '5108010001', 'type' => 'kelurahan'],
                ['name' => 'Banyuning',        'code' => '5108010002', 'type' => 'kelurahan'],
                ['name' => 'Beratan',          'code' => '5108010003', 'type' => 'kelurahan'],
                ['name' => 'Bululeng',         'code' => '5108010004', 'type' => 'kelurahan'],
                ['name' => 'Kampung Anyar',    'code' => '5108010005', 'type' => 'kelurahan'],
                ['name' => 'Kampung Baru',     'code' => '5108010006', 'type' => 'kelurahan'],
                ['name' => 'Kampung Bugis',    'code' => '5108010007', 'type' => 'kelurahan'],
                ['name' => 'Kampung Kajanan',  'code' => '5108010008', 'type' => 'kelurahan'],
                ['name' => 'Kampung Singaraja', 'code' => '5108010009', 'type' => 'kelurahan'],
                ['name' => 'Kendran',          'code' => '5108010010', 'type' => 'kelurahan'],
                ['name' => 'Kaliuntu',         'code' => '5108010011', 'type' => 'kelurahan'],
                ['name' => 'Liligundi',        'code' => '5108010012', 'type' => 'kelurahan'],
                ['name' => 'Paket Agung',      'code' => '5108010013', 'type' => 'kelurahan'],
                ['name' => 'Penarukan',        'code' => '5108010014', 'type' => 'kelurahan'],
                ['name' => 'Penglatan',        'code' => '5108010015', 'type' => 'desa'],
                ['name' => 'Petandakan',       'code' => '5108010016', 'type' => 'desa'],
                ['name' => 'Sari Mekar',       'code' => '5108010017', 'type' => 'desa'],
                ['name' => 'Singaraja',        'code' => '5108010018', 'type' => 'kelurahan'],
                ['name' => 'Sukasada',         'code' => '5108010019', 'type' => 'kelurahan'],
                ['name' => 'Tukadmungga',      'code' => '5108010020', 'type' => 'desa'],
            ],
            'Sukasada' => [
                ['name' => 'Ambengan',         'code' => '5108020001', 'type' => 'desa'],
                ['name' => 'Gitgit',           'code' => '5108020002', 'type' => 'desa'],
                ['name' => 'Kayuputih',        'code' => '5108020003', 'type' => 'desa'],
                ['name' => 'Pancasari',        'code' => '5108020004', 'type' => 'desa'],
                ['name' => 'Panji',            'code' => '5108020005', 'type' => 'desa'],
                ['name' => 'Panji Anom',       'code' => '5108020006', 'type' => 'desa'],
                ['name' => 'Pegadungan',       'code' => '5108020007', 'type' => 'desa'],
                ['name' => 'Sambangan',        'code' => '5108020008', 'type' => 'desa'],
                ['name' => 'Selat',            'code' => '5108020009', 'type' => 'desa'],
                ['name' => 'Sukasada',         'code' => '5108020010', 'type' => 'desa'],
                ['name' => 'Tegallinggah',     'code' => '5108020011', 'type' => 'desa'],
                ['name' => 'Wanagiri',         'code' => '5108020012', 'type' => 'desa'],
            ],
            'Sawan' => [
                ['name' => 'Bungkulan',        'code' => '5108030001', 'type' => 'desa'],
                ['name' => 'Galungan',         'code' => '5108030002', 'type' => 'desa'],
                ['name' => 'Jagaraga',         'code' => '5108030003', 'type' => 'desa'],
                ['name' => 'Kerobokan',        'code' => '5108030004', 'type' => 'desa'],
                ['name' => 'Lemukih',          'code' => '5108030005', 'type' => 'desa'],
                ['name' => 'Menyali',          'code' => '5108030006', 'type' => 'desa'],
                ['name' => 'Sangsit',          'code' => '5108030007', 'type' => 'desa'],
                ['name' => 'Sawan',            'code' => '5108030008', 'type' => 'desa'],
                ['name' => 'Sekumpul',         'code' => '5108030009', 'type' => 'desa'],
                ['name' => 'Sinabun',          'code' => '5108030010', 'type' => 'desa'],
                ['name' => 'Sudaji',           'code' => '5108030011', 'type' => 'desa'],
                ['name' => 'Tikuaya',          'code' => '5108030012', 'type' => 'desa'],
            ],
            'Kubutambahan' => [
                ['name' => 'Bangah',           'code' => '5108040001', 'type' => 'desa'],
                ['name' => 'Bila',             'code' => '5108040002', 'type' => 'desa'],
                ['name' => 'Bondalem',         'code' => '5108040003', 'type' => 'desa'],
                ['name' => 'Bukti',            'code' => '5108040004', 'type' => 'desa'],
                ['name' => 'Depeha',           'code' => '5108040005', 'type' => 'desa'],
                ['name' => 'Kayu Putih',       'code' => '5108040006', 'type' => 'desa'],
                ['name' => 'Kubutambahan',     'code' => '5108040007', 'type' => 'desa'],
                ['name' => 'Mengening',        'code' => '5108040008', 'type' => 'desa'],
                ['name' => 'Pacung',           'code' => '5108040009', 'type' => 'desa'],
                ['name' => 'Tambakan',         'code' => '5108040010', 'type' => 'desa'],
                ['name' => 'Tegalasah',        'code' => '5108040011', 'type' => 'desa'],
            ],
            'Tejakula' => [
                ['name' => 'Bondalem',         'code' => '5108050001', 'type' => 'desa'],
                ['name' => 'Batuampar',        'code' => '5108050002', 'type' => 'desa'],
                ['name' => 'Julah',            'code' => '5108050003', 'type' => 'desa'],
                ['name' => 'Les',              'code' => '5108050004', 'type' => 'desa'],
                ['name' => 'Madenan',          'code' => '5108050005', 'type' => 'desa'],
                ['name' => 'Pacung',           'code' => '5108050006', 'type' => 'desa'],
                ['name' => 'Penuktukan',       'code' => '5108050007', 'type' => 'desa'],
                ['name' => 'Sambirenteng',     'code' => '5108050008', 'type' => 'desa'],
                ['name' => 'Tejakula',         'code' => '5108050009', 'type' => 'desa'],
            ],
            'Gerokgak' => [
                ['name' => 'Banyupoh',         'code' => '5108060001', 'type' => 'desa'],
                ['name' => 'Celukan Bawang',   'code' => '5108060002', 'type' => 'desa'],
                ['name' => 'Gerokgak',         'code' => '5108060003', 'type' => 'desa'],
                ['name' => 'Musi',             'code' => '5108060004', 'type' => 'desa'],
                ['name' => 'Patas',            'code' => '5108060005', 'type' => 'desa'],
                ['name' => 'Pejarakan',        'code' => '5108060006', 'type' => 'desa'],
                ['name' => 'Pengulon',         'code' => '5108060007', 'type' => 'desa'],
                ['name' => 'Sanggalangit',     'code' => '5108060008', 'type' => 'desa'],
                ['name' => 'Sumberklampok',    'code' => '5108060009', 'type' => 'desa'],
                ['name' => 'Tinga Tinga',      'code' => '5108060010', 'type' => 'desa'],
                ['name' => 'Titab',            'code' => '5108060011', 'type' => 'desa'],
            ],
            'Seririt' => [
                ['name' => 'Bestala',          'code' => '5108070001', 'type' => 'desa'],
                ['name' => 'Bubunan',          'code' => '5108070002', 'type' => 'desa'],
                ['name' => 'Kalianget',        'code' => '5108070003', 'type' => 'desa'],
                ['name' => 'Kalisada',         'code' => '5108070004', 'type' => 'desa'],
                ['name' => 'Lokapaksa',        'code' => '5108070005', 'type' => 'desa'],
                ['name' => 'Munduk',           'code' => '5108070006', 'type' => 'desa'],
                ['name' => 'Patemon',          'code' => '5108070007', 'type' => 'desa'],
                ['name' => 'Pengastulan',      'code' => '5108070008', 'type' => 'desa'],
                ['name' => 'Seririt',          'code' => '5108070009', 'type' => 'kelurahan'],
                ['name' => 'Sulanyah',         'code' => '5108070010', 'type' => 'desa'],
                ['name' => 'Tangguwisia',      'code' => '5108070011', 'type' => 'desa'],
                ['name' => 'Umedesa',          'code' => '5108070012', 'type' => 'desa'],
                ['name' => 'Unggahan',         'code' => '5108070013', 'type' => 'desa'],
            ],
            'Banjar' => [
                ['name' => 'Banyuatis',        'code' => '5108080001', 'type' => 'desa'],
                ['name' => 'Banjar',           'code' => '5108080002', 'type' => 'desa'],
                ['name' => 'Banjar Tegeha',    'code' => '5108080003', 'type' => 'desa'],
                ['name' => 'Cempaga',          'code' => '5108080004', 'type' => 'desa'],
                ['name' => 'Dencarik',         'code' => '5108080005', 'type' => 'desa'],
                ['name' => 'Gesing',           'code' => '5108080006', 'type' => 'desa'],
                ['name' => 'Kayuputih',        'code' => '5108080007', 'type' => 'desa'],
                ['name' => 'Kaliasem',         'code' => '5108080008', 'type' => 'desa'],
                ['name' => 'Munduk',           'code' => '5108080009', 'type' => 'desa'],
                ['name' => 'Pedawa',           'code' => '5108080010', 'type' => 'desa'],
                ['name' => 'Sidetapa',         'code' => '5108080011', 'type' => 'desa'],
                ['name' => 'Tampekan',         'code' => '5108080012', 'type' => 'desa'],
                ['name' => 'Temukus',          'code' => '5108080013', 'type' => 'desa'],
                ['name' => 'Tigawasa',         'code' => '5108080014', 'type' => 'desa'],
                ['name' => 'Tirtasari',        'code' => '5108080015', 'type' => 'desa'],
            ],
            'Busungbiu' => [
                ['name' => 'Busungbiu',        'code' => '5108090001', 'type' => 'desa'],
                ['name' => 'Kekeran',          'code' => '5108090002', 'type' => 'desa'],
                ['name' => 'Kepuh',            'code' => '5108090003', 'type' => 'desa'],
                ['name' => 'Kombang',          'code' => '5108090004', 'type' => 'desa'],
                ['name' => 'Lumbang',          'code' => '5108090005', 'type' => 'desa'],
                ['name' => 'Pelapuan',         'code' => '5108090006', 'type' => 'desa'],
                ['name' => 'Pucaksari',        'code' => '5108090007', 'type' => 'desa'],
                ['name' => 'Sepang',           'code' => '5108090008', 'type' => 'desa'],
                ['name' => 'Subuk',            'code' => '5108090009', 'type' => 'desa'],
                ['name' => 'Tista',            'code' => '5108090010', 'type' => 'desa'],
                ['name' => 'Tinggarsari',      'code' => '5108090011', 'type' => 'desa'],
            ],
        ];

        foreach ($data as $kecamatanName => $villages) {
            $kecamatan = Kecamatan::where('name', $kecamatanName)->first();

            if (!$kecamatan) {
                $this->command->warn("Kecamatan '{$kecamatanName}' tidak ditemukan, skip.");
                continue;
            }

            foreach ($villages as $village) {
                Village::firstOrCreate(
                    ['code' => $village['code']],
                    [
                        'kecamatan_id' => $kecamatan->id,
                        'name'         => $village['name'],
                        'type'         => $village['type'],
                        'is_active'    => true,
                    ]
                );
            }

            $this->command->info("✓ {$kecamatanName}: " . count($villages) . " desa/kelurahan");
        }
    }
}

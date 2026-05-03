<?php

namespace Database\Seeders;

use App\Models\PsksCategory;
use Illuminate\Database\Seeder;

class PsksCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Berbasis Individu/Jiwa
            ['code' => 'PSKS-J-01', 'name' => 'Pekerja Sosial Masyarakat (PSM)',        'subject_type' => 'person'],
            ['code' => 'PSKS-J-02', 'name' => 'Tenaga Kesejahteraan Sosial Kecamatan (TKSK)', 'subject_type' => 'person'],
            ['code' => 'PSKS-J-03', 'name' => 'Relawan Sosial',                          'subject_type' => 'person'],
            ['code' => 'PSKS-J-04', 'name' => 'Penyuluh Sosial',                         'subject_type' => 'person'],
            ['code' => 'PSKS-J-05', 'name' => 'Taruna Siaga Bencana (TAGANA)',           'subject_type' => 'person'],
            ['code' => 'PSKS-J-06', 'name' => 'Wanita Pemimpin Kesejahteraan Sosial',    'subject_type' => 'person'],

            // Berbasis Lembaga
            ['code' => 'PSKS-L-01', 'name' => 'Karang Taruna',                           'subject_type' => 'institution'],
            ['code' => 'PSKS-L-02', 'name' => 'Lembaga Kesejahteraan Sosial (LKS)',      'subject_type' => 'institution'],
            ['code' => 'PSKS-L-03', 'name' => 'Lembaga Konsultasi Kesejahteraan Keluarga (LK3)', 'subject_type' => 'institution'],
            ['code' => 'PSKS-L-04', 'name' => 'Lembaga Kesejahteraan Sosial Anak (LKSA)', 'subject_type' => 'institution'],
            ['code' => 'PSKS-L-05', 'name' => 'PKK',                                     'subject_type' => 'institution'],
            ['code' => 'PSKS-L-06', 'name' => 'Organisasi Sosial (Orsos)',               'subject_type' => 'institution'],
        ];

        foreach ($categories as $data) {
            PsksCategory::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
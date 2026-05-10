<?php

namespace Database\Seeders;

use App\Models\PmksCategory;
use Illuminate\Database\Seeder;

class PmksCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Usia 0-5 tahun
            ['code' => 'PMKS-01', 'name' => 'ANAK BALITA TERLANTAR',                                          'min_age' => 0,  'max_age' => 5,   'gender_restriction' => null],
            // Usia 6-18 tahun
            ['code' => 'PMKS-02', 'name' => 'ANAK TERLANTAR',                                                 'min_age' => 6,  'max_age' => 18,  'gender_restriction' => null],
            ['code' => 'PMKS-03', 'name' => 'ANAK YG BERHADAPAN DENGAN HUKUM',                                'min_age' => 6,  'max_age' => 18,  'gender_restriction' => null],
            ['code' => 'PMKS-04', 'name' => 'ANAK JALANAN',                                                   'min_age' => 6,  'max_age' => 18,  'gender_restriction' => null],
            ['code' => 'PMKS-05', 'name' => 'ANAK DENGAN KEDISABILITASAN',                                    'min_age' => 6,  'max_age' => 18,  'gender_restriction' => null],
            ['code' => 'PMKS-06', 'name' => 'ANAK YG MENJADI KORBAN TINDAK KEKERASAN ATAU DIPERLAKUKAN SALAH','min_age' => 6,  'max_age' => 18,  'gender_restriction' => null],
            ['code' => 'PMKS-07', 'name' => 'ANAK YG MEMERLUKAN PERLINDUNGAN KHUSUS',                         'min_age' => 6,  'max_age' => 18,  'gender_restriction' => null],
            // Usia 60 tahun ke atas
            ['code' => 'PMKS-08', 'name' => 'LANJUT USIA TERLANTAR',                                          'min_age' => 60, 'max_age' => null,'gender_restriction' => null],
            // Tanpa batasan usia
            ['code' => 'PMKS-09', 'name' => 'PENYANDANG DISABILITAS',                                         'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-10', 'name' => 'TUNA SUSILA',                                                    'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-11', 'name' => 'GELANDANGAN',                                                    'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-12', 'name' => 'PENGEMIS',                                                       'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-13', 'name' => 'PEMULUNG',                                                       'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-14', 'name' => 'KELOMPOK MINORITAS',                                             'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-15', 'name' => 'BEKAS WARGA BINAAN LEMBAGA PEMASYARAKATAN (BWBLP)',               'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-16', 'name' => 'ORANG DENGAN HIV/AIDS',                                          'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-17', 'name' => 'KORBAN PENYALAHGUNAAN NAPZA',                                    'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-18', 'name' => 'KORBAN TRAFFICKING',                                             'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-19', 'name' => 'KORBAN TINDAK KEKERASAN ATAU YANG DIPERLAKUKAN SALAH',           'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-20', 'name' => 'PEKERJA MIGRAN BERMASALAH SOSIAL',                               'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-21', 'name' => 'KORBAN BENCANA ALAM',                                            'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-22', 'name' => 'KORBAN BENCANA SOSIAL',                                          'min_age' => null,'max_age' => null,'gender_restriction' => null],
            // Khusus perempuan
            ['code' => 'PMKS-23', 'name' => 'PEREMPUAN RAWAN SOSIAL EKONOMI',                                 'min_age' => null,'max_age' => null,'gender_restriction' => 'P'],
            // Tanpa batasan
            ['code' => 'PMKS-24', 'name' => 'FAKIR MISKIN',                                                   'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-25', 'name' => 'KELUARGA BERMASALAH SOSIAL PSIKOLOGIS',                          'min_age' => null,'max_age' => null,'gender_restriction' => null],
            ['code' => 'PMKS-26', 'name' => 'KOMUNITAS ADAT TERPENCIL',                                       'min_age' => null,'max_age' => null,'gender_restriction' => null],
        ];

        foreach ($categories as $data) {
            PmksCategory::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}

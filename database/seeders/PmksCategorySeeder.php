<?php

namespace Database\Seeders;

use App\Models\PmksCategory;
use Illuminate\Database\Seeder;

class PmksCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'PMKS-01', 'name' => 'ANAK BALITA TERLANTAR'],
            ['code' => 'PMKS-02', 'name' => 'ANAK TERLANTAR'],
            ['code' => 'PMKS-03', 'name' => 'ANAK YG BERHADAPAN DENGAN HUKUM'],
            ['code' => 'PMKS-04', 'name' => 'ANAK JALANAN'],
            ['code' => 'PMKS-05', 'name' => 'ANAK DENGAN KEDISABILITASAN'],
            ['code' => 'PMKS-06', 'name' => 'ANAK YG MENJADI KORBAN TINDAK KEKERASAN ATAU DIPERLAKUKAN SALAH'],
            ['code' => 'PMKS-07', 'name' => 'ANAK YG MEMERLUKAN PERLIDUNGAN KHUSUS'],
            ['code' => 'PMKS-08', 'name' => 'LANJUT USIA TERLANTAR'],
            ['code' => 'PMKS-09', 'name' => 'PENYANDANG DISABILITAS'],
            ['code' => 'PMKS-10', 'name' => 'TUNA SUSILA'],
            ['code' => 'PMKS-11', 'name' => 'GELANDANGAN'],
            ['code' => 'PMKS-12', 'name' => 'PENGEMIS'],
            ['code' => 'PMKS-13', 'name' => 'PEMULUNG'],
            ['code' => 'PMKS-14', 'name' => 'KELOMPOK MINORITAS'],
            ['code' => 'PMKS-15', 'name' => 'BEKAS WARGA BINAAN LEMBAGA PEMASYARAKATAN (BWBLP)'],
            ['code' => 'PMKS-16', 'name' => 'ORANG DENGAN HIV/ AIDS'],
            ['code' => 'PMKS-17', 'name' => 'KORBAN PENYALHGUNAAN NAPZA'],
            ['code' => 'PMKS-18', 'name' => 'KORBAN TRAFICKING'],
            ['code' => 'PMKS-19', 'name' => 'KORBAN TINDAK KEKERASAN ATAU YANG DIPERLAKUKAN SALAH'],
            ['code' => 'PMKS-20', 'name' => 'PEKERJA MIGRAN BERMASALAH SOSIAL'],
            ['code' => 'PMKS-21', 'name' => 'KORBAN BENCANA ALAM'],
            ['code' => 'PMKS-22', 'name' => 'KORBAN BENCANA SOSIAL'],
            ['code' => 'PMKS-23', 'name' => 'FPEREMPUAN RAWAN SOSIAL EKONOMI'],
            ['code' => 'PMKS-24', 'name' => 'FAKIR MISKIN'],
            ['code' => 'PMKS-25', 'name' => 'KELUARGA BERMASALAH SOSIAL PSIKOLOGIS'],
            ['code' => 'PMKS-26', 'name' => 'KOMUNITAS ADAT TERPENCIL'],
        ];

        foreach ($categories as $data) {
            PmksCategory::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
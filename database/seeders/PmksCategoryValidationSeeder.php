<?php

namespace Database\Seeders;

use App\Models\PmksCategory;
use Illuminate\Database\Seeder;

class PmksCategoryValidationSeeder extends Seeder
{
    private const RULES = [
        'PMKS-01' => ['min_age' => 0,  'max_age' => 5,    'gender_restriction' => null],
        'PMKS-02' => ['min_age' => 6,  'max_age' => 18,   'gender_restriction' => null],
        'PMKS-03' => ['min_age' => 6,  'max_age' => 18,   'gender_restriction' => null],
        'PMKS-04' => ['min_age' => 6,  'max_age' => 18,   'gender_restriction' => null],
        'PMKS-05' => ['min_age' => 6,  'max_age' => 18,   'gender_restriction' => null],
        'PMKS-06' => ['min_age' => 6,  'max_age' => 18,   'gender_restriction' => null],
        'PMKS-07' => ['min_age' => 6,  'max_age' => 18,   'gender_restriction' => null],
        'PMKS-08' => ['min_age' => 60, 'max_age' => null,  'gender_restriction' => null],
        'PMKS-23' => ['min_age' => null, 'max_age' => null, 'gender_restriction' => 'P'],
    ];

    public function run(): void
    {
        foreach (self::RULES as $code => $rules) {
            PmksCategory::where('code', $code)->update($rules);
        }
        $this->command->info('Aturan validasi PMKS berhasil dimigrasikan ke database.');
    }
}
